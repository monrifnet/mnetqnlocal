<?php
/*
Plugin Name: MonrifNet QN Local Plugin
Plugin URI:  https://monrifnet.github.io/mnetqnlocal/
Description: Monrif Net WordPress plugin for external site integration to Quotidiano.net
Version:     20170209
Author:      Monrif Net
Author URI:  http://www.monrif.net/
License:     Commercial

{Plugin Name} è un software commerciale. Ne sono vietate
la modifica e la distribuzione senza esplicito consenso
dei fornitori.
*/
defined('ABSPATH') or die('These violent delights have violent ends');

define('MNETQNLOCAL_VER', 20170209);
define('MNETQNLOCAL_DIR', plugin_dir_path(__FILE__));
define('MNETQNLOCAL_RES_DIR', MNETQNLOCAL_DIR . 'res/');
define('MNETQNLOCAL_RES_URL', plugins_url('res/', __FILE__));

@include_once MNETQNLOCAL_DIR . 'admin.inc.php';

global $mnetqnlocal_css, $mnetqnlocal_js;
$mnetqnlocal_css = array(
    "header" => MNETQNLOCAL_RES_URL . "header.css",
);
$mnetqnlocal_js = array(
    // inserire qui i JS, formati come i CSS
);

add_action('wp_enqueue_scripts', 'mnetqnlocal_cssjs', 9999);
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

add_action('wp_footer', 'mnetqnlocal_footer');
function mnetqnlocal_footer() {
    @include_once MNETQNLOCAL_RES_DIR . 'header-qn-network.html';
    @include_once MNETQNLOCAL_RES_DIR . 'wp-mnetlocal-wtk-widget.php';
}
