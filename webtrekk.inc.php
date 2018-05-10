<?php

# Plugging of the Webtrekk plugin analytics

add_action('wp_footer', 'mql_wtk_footer');
function mql_wtk_footer() {
    @include_once MNETQNLOCAL_RES_DIR . 'wp-mnetlocal-wtk-widget.php';
}

function mql_wtk_properties($addslashes = FALSE) {
    global $post;
    $override_wtk = mql_wtk_parse_properties();
    $wtk = mql_wtk_default_properties();
    foreach ($wtk as $type => $values) {
        if (!count($override_wtk[$type])) continue;
        foreach ($values as $k => $v) {
            if (empty(@$override_wtk[$type][$k])) continue;
            $wtk[$type][$k] = $override_wtk[$type][$k];
        }
    }
    $pagetype = "home";
    $category = "";
    $keywords = "";
    $author = "";
    if (is_singular()) {
        $author = get_the_author();
        $tags = array();
        $the_tags = get_the_tags();
        if ($the_tags && count($the_tags)) {
            foreach (get_the_tags() as $tag) {
                $tags[] = $tag->name;
            }
        }
        $keywords = implode(",", $tags);
        $category = @get_the_category();
        if ($category and count($category)) {
            $category = $category[0]->name;
        } else {
            $category = "";
        }
        $pagetype = "articolo";
        switch ($post->post_type) {
            case "post":
                switch (get_post_format()) {
                    case "video":
                        $pagetype = "video";
                        break 2;
                    case "gallery":
                        $pagetype = "foto";
                        break 2;
                }
                break;
            case "event":
                $pagetype = "evento";
                break;
            case "recipe":
                $pagetype = "ricetta";
                break;
        }
    } elseif (is_archive()) {
        $pagetype = "sezione";
        if (is_category()) {
            $category = single_cat_title('', FALSE);
        }
    }
    $refresh = "no-refresh";
    if (isset($_GET["refresh_ce"])) {
        $refresh = "refresh_ce";
    }
    $wtk["cg"][5] = $pagetype;
    $wtk["cg"][6] = $category;
    $wtk["cp"][1] = wp_title(" - ", FALSE, "right");
    $wtk["cp"][2] = $refresh;
    $wtk["cp"][3] = $category;
    $wtk["cp"][7] = $pagetype;
    $wtk["cp"][13] = $keywords;
    $wtk["cp"][15] = $author;
    $max_keys = array(
        "cg" => 6,
        "cp" => 25,
    );
    foreach ($max_keys as $type => $n) {
        for ($i = 1; $i <= $n; $i++) {
            if (empty($wtk[$type][$i])) {
                $wtk[$type][$i] = "na";
            } elseif ($addslashes === TRUE) {
                $wtk[$type][$i] = addslashes($wtk[$type][$i]);
            }
        }
    }
    ksort ($wtk["cg"], SORT_NUMERIC);
    ksort ($wtk["cp"], SORT_NUMERIC);
    return $wtk;
}

function mql_wtk_default_properties() {
    $url = get_site_option("home", "na");
    $site = preg_replace('@^(?:https?://)?|(?:\.localmente)?\.(?:it|com|net|org)/?$@i', '', $url);
    $site = preg_replace('@^(?:[^.]+\.)*([^.]+)$@i', '$1', $site);
    $wtk = array(
        "cg" => array(
            1 => "Aggregato Speed",
            2 => "qn-local",
            3 => "$site",
        ),
        "cp" => array(
            4 => "external.site.{$site}",
        ),
    );
    return $wtk;
}

function mql_wtk_parse_properties($fromtext = FALSE) {
    if ($fromtext === FALSE) {
        $fromtext = get_site_option(MNETQNLOCAL_OPT_OVERRIDE_WTK, '');
    }
    $wtk = array(
        "cg" => array(),
        "cp" => array(),
    );
    $fromtext = trim($fromtext);
    if ($fromtext) {
        foreach (preg_split('/[\n\r]+/', $fromtext) as $line) {
            if (preg_match('/^([a-z]+)0*(\d+):\s*(.+)/i', trim($line), $parsed)) {
                list($m, $type, $k, $v) = $parsed;
                if (array_key_exists($type, $wtk)) {
                    $wtk[$type][(int)$k] = $v;
                }
            }
        }
    }
    return $wtk;
}
