<?php
/*
Plugin Name: MonrifNet QN Local Plugin
Plugin URI:  https://monrifnet.github.io/mnetqnlocal/
Description: Monrif Net WordPress plugin for external site integration to Quotidiano.net
Version:     20180223
Author:      Monrif Net
Author URI:  http://www.monrif.net/
License:     Commercial

{Plugin Name} è un software commerciale. Ne sono vietate
la modifica e la distribuzione senza esplicito consenso
dei fornitori.
*/
defined('ABSPATH') or die('These violent delights have violent ends');

define('MNETQNLOCAL_VER', 20180223);
define('MNETQNLOCAL_DIR', plugin_dir_path(__FILE__));
define('MNETQNLOCAL_RES_DIR', MNETQNLOCAL_DIR . 'res/');
define('MNETQNLOCAL_RES_URL', plugins_url('res/', __FILE__));

@include_once MNETQNLOCAL_DIR . 'admin.inc.php';
@include_once MNETQNLOCAL_DIR . 'recaptcha.inc.php';
@include_once MNETQNLOCAL_DIR . 'event_manager.inc.php';
@include_once MNETQNLOCAL_DIR . 'webtrekk.inc.php';
@include_once MNETQNLOCAL_DIR . 'amp.inc.php';

global $mnetqnlocal_css, $mnetqnlocal_js;
$mnetqnlocal_css = array(
    // inserire qui i CSS dalla dir /res, e.g.
    // "id_css" => MNETQNLOCAL_RES_URL . "style.css",
);
$mnetqnlocal_js = array(
    // inserire qui i JS, formati come i CSS
);

add_action('wp_enqueue_scripts', 'mnetqnlocal_cssjs', 1);
function mnetqnlocal_cssjs() {
    global $mnetqnlocal_css, $mnetqnlocal_js;
    if (!empty($mnetqnlocal_css) && is_array($mnetqnlocal_css)) {
        foreach ($mnetqnlocal_css as $handle => $css) {
            wp_enqueue_style("mnetqnlocal_{$handle}", $css, NULL, MNETQNLOCAL_VER);
        }
    }
    if (!empty($mnetqnlocal_js) && is_array($mnetqnlocal_js)) {
        foreach ($mnetqnlocal_js as $handle => $js) {
            wp_enqueue_style("mnetqnlocal_{$handle}", $js, NULL, MNETQNLOCAL_VER, true);
        }
    }
}

function mnetqnlocal_addslashes(&$item, $key, $what = FALSE) {
    if (!$what) $item = addslashes($item);
    else $item = str_replace($what, "\\{$what}", $item);
}

function mnetqnlocal_head() {
    $cat = FALSE;
    if (is_single()) {
        $categories = get_the_category();
        if ($categories && is_array($categories)) {
            $cat = array();
            foreach ($categories as $category) {
                $cat[] = $category->slug;
            }
        } else {
            $cat = get_post_meta($post->ID, "dfp_category");
        }
    } elseif (in_array(get_post_type(), array("page", "event", "recipe"))) {
        global $post;
        $cat = get_post_meta($post->ID, "dfp_category");
    } elseif (is_category()) {
        $term = get_queried_object();
        $cat = $term->slug;
    }
    if (!$cat) return;
    if (!is_array($cat)) $cat = array( $cat );
    array_walk($cat, "mnetqnlocal_addslashes", "'");
    $cat = "[ '" . implode("', '", $cat) . "' ]";
    echo <<< HereJs
        <script type="text/javascript">
            var dfp_targeting = dfp_targeting || {};
            dfp_targeting["category"] = {$cat};
        </script>
HereJs;
}
add_action('wp_head', 'mnetqnlocal_head', 1);

add_action('wp_footer', 'mnetqnlocal_footer');
function mnetqnlocal_footer() {
    @include_once MNETQNLOCAL_RES_DIR . 'header-qn-network.php';
}

/*
 * Modifiche RSS richieste da LecceNews24:
 * - category_url
 * - image_description
 * - city
 */
function mnetqnlocal_feed_integrate() {
	global $post, $wpdb;
    $enclosures = array();
    $cats = get_the_category();
    if (gettype($cats) == "array" && count($cats)) {
        $cat = array_shift($cats);
        $enclosures["category_url"] = get_category_link($cat->term_id);
    }
    $cities = get_the_terms($post, "locations");
    if (gettype($cities) == "array" && count($cities)) {
        $city = array_shift($cities);
        $enclosures["city"] = $city->name;
    }
    $img_url = $img_desc = FALSE;
    if (($img_id = get_post_thumbnail_id($post->ID))) {
        $img_desc = @get_the_title($img_id);
    } elseif (($imgs = get_attached_media("image", $post->ID)) && gettype($imgs) == "array" && count($imgs)) {
        $img = array_shift($imgs);
        $img_id = @$img->ID;
        $img_desc = @$img->post_title;
    }
    if ($img_id) $img_url = wp_get_attachment_image_url($img_id, 'large');
    if ($img_desc) $enclosures["image_description"] = $img_desc;
    if ($img_url) $enclosures["image"] = $img_url;
    $enc_str = "\n\t\t" . '<enclosure type="%s" url="%s" length="%d" />';
    foreach($enclosures as $enc_type => $enc_url) {
        $enc_type = htmlspecialchars($enc_type);
        $enc_url = htmlspecialchars($enc_url);
        printf($enc_str, $enc_type, $enc_url, strlen($enc_url));
    }
    echo PHP_EOL;
}
add_action( "rss_item", "mnetqnlocal_feed_integrate" );
add_action( "rss2_item", "mnetqnlocal_feed_integrate" );

function mnetqnlocal_excerpt_feed_filter($excerpt="") {
    return preg_replace('/<img\s[^>]+>/i', '', $excerpt);
}
add_filter( "the_excerpt_rss", "mnetqnlocal_excerpt_feed_filter" );

function mnetqnlocal_post_updated($data , $postarr) {
    if (get_site_option(MNETQNLOCAL_OPT_FULLURLEDIT, TRUE)) {
        global $post;
        # https://codex.wordpress.org/Plugin_API/Filter_Reference/wp_insert_post_data
        $is_public = $post->post_status == "publish" && $data["post_status"] == "publish";
        $has_changed = $post->post_name != $data["post_name"];
        if ($is_public && $has_changed) {
            // impedisce modifiche all'URL se il post è già pubblico
            $data["post_name"] = $post->post_name;
        }
    }
    return $data;
}
add_filter( 'wp_insert_post_data', 'mnetqnlocal_post_updated', 99, 2 );

function mnetqnlocal_ajax_inline_save() {
    if (!(int)get_site_option(MNETQNLOCAL_OPT_FULLURLEDIT, TRUE)) return;
    if (!isset($_POST['post_ID']) || !($post_ID = (int)$_POST['post_ID']))
        wp_die();
    $data = &$_POST;
    $post = get_post($post_ID);
    $is_public = $post->post_status == "publish" && $data["_status"] == "publish";
    $has_changed = $post->post_name != $data["post_name"];
    if ($is_public && $has_changed) {
        // impedisce modifiche all'URL se il post è già pubblico
        $_POST["post_name"] = $post->post_name;
    }
}
add_action( "wp_ajax_inline-save", "mnetqnlocal_ajax_inline_save", 0 );
