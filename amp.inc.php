<?php

# Plugging of the AMP plugin and sidebars

function mql_amp_sidebars($empty = '') {
    $sidebars = array(
        'amp-header'        => 'AMP Header',
        'amp-above-photo'   => 'AMP Above Photo',
        'amp-below-photo'   => 'AMP Below Photo',
        'amp-after-text'    => 'AMP After Text',
        'amp-after-content' => 'AMP After Content',
        'amp-footer'        => 'AMP Footer',
    );
    foreach ($sidebars as $id => $name) {
        register_sidebar(array(
            'name' => $name,
            'id'   => $id,
            'before_widget' => '',
            'after_widget'  => '',
            'before_title'  => '',
            'after_title'   => '',
        ));
    }
}
add_action( 'widgets_init', 'mql_amp_sidebars' );

function mql_amp_post_template_add_schemaorg_metadata( $amp_template ) {
    //var_dump($amp_template);
}
// add_action( 'amp_post_template_head', 'mql_amp_post_template_add_schemaorg_metadata', 1 );

function mql_amp_post_template_metadata( $metadata, $post ) {
    var_dump($metadata);
    return $metadata;
}
// add_filter( 'amp_post_template_metadata', 'mql_amp_post_template_metadata', 1, 2 );

function mql_amp_header() {
    dynamic_sidebar( 'amp-header' );
}
add_action( 'amp_post_template_header', 'mql_amp_header' );

function mql_amp_above_photo() {
    dynamic_sidebar( 'amp-above-photo' );
}
add_action( 'amp_post_template_above_photo', 'mql_amp_above_photo' );

function mql_amp_below_photo() {
    dynamic_sidebar( 'amp-below-photo' );
}
add_action( 'amp_post_template_below_photo', 'mql_amp_below_photo' );

function mql_amp_after_text() {
    dynamic_sidebar( 'amp-after-text' );
}
add_action( 'amp_post_template_after_text', 'mql_amp_after_text' );

function mql_amp_after_content() {
    dynamic_sidebar( 'amp-after-content' );
}
add_action( 'amp_post_template_after_content', 'mql_amp_after_content' );

function mql_amp_footer() {
    dynamic_sidebar( 'amp-footer' );
    $wtk = mql_wtk_properties(TRUE);
    $extra_cp_k = 11;
    $last_cp_k = array_pop(array_keys($wtk["cp"]));
    $extra_cp = "";
    for ($i = $extra_cp_k; $i <= $last_cp_k; $i++) {
        if (!array_key_exists($i, $wtk["cp"])) continue;
        if ($i > $extra_cp_k) $extra_cp .= str_repeat(" ", 4*3);
        $extra_cp .= sprintf('"cp%d": "%s"', $i, $wtk["cp"][$i]);
        if ($i < $last_cp_k) $extra_cp .= "," . PHP_EOL;
    }
    echo <<<HereAMP
<amp-analytics type="webtrekk">
    <script type="application/json">
      {
        "transport": {"beacon": false, "xhrpost": false, "image": true},
        "requests": {
            "specialPageview": "\${pageview}"
        },
        "extraUrlParams": {
            {$extra_cp}
        },
        "vars": {
            "trackDomain": "monrifitalia01.wt-eu02.net",
            "trackId": "754447428866385",
            "contentId": "AMPDOC_URL"
        },
        "triggers": {
            "trackPageview": {
                "on": "visible",
                "request": "specialPageview",
                "vars": {
                    "pageCategory1": "{$wtk["cg"][1]}",
                    "pageCategory2": "{$wtk["cg"][2]}",
                    "pageCategory3": "{$wtk["cg"][3]}",
                    "pageCategory4": "{$wtk["cg"][4]}",
                    "pageCategory5": "{$wtk["cg"][5]}",
                    "pageCategory6": "{$wtk["cg"][6]}",
                    "pageParameter1": "TITLE",
                    "pageParameter2": "amp",
                    "pageParameter3": "{$wtk["cp"][3]}",
                    "pageParameter4": "{$wtk["cp"][4]}",
                    "pageParameter5": "{$wtk["cp"][5]}",
                    "pageParameter6": "{$wtk["cp"][6]}",
                    "pageParameter7": "amp",
                    "pageParameter8": "{$wtk["cp"][8]}",
                    "pageParameter9": "{$wtk["cp"][9]}",
                    "pageParameter10": "{$wtk["cp"][10]}"
                }
            }
        }
      }
    </script>
</amp-analytics>
HereAMP;
}
add_action( 'amp_post_template_footer', 'mql_amp_footer' );
