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
}
add_action( 'amp_post_template_footer', 'mql_amp_footer' );
