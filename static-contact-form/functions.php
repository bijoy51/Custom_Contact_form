<?php
function staticform_enqueue_styles() {
    wp_enqueue_style('style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'staticform_enqueue_styles');
