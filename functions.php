<?php

/**
 * Additional code for the child theme goes in here.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99);

function enqueue_child_styles() {
	$css_creation = filectime(get_stylesheet_directory() . '/style.css');

	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [], $css_creation );
}

/**
 * Include all needed scripts to be used by EveryAction signup & contact information forms
 */
add_action( 'wp_enqueue_scripts', 'enqueue_every_action_form_scripts', 99);

function enqueue_every_action_form_scripts() {
	wp_enqueue_script('everyaction-js','https://static.everyaction.com/ea-actiontag/at.js',);
	wp_enqueue_style( 'everyaction-styles', 'https://static.everyaction.com/ea-actiontag/at.min.css' );
}

require_once 'wp_all_import_functions.php';

/**
 * Suppress Yoast SEO author metadata that are not aware of Author override.
 */
add_filter('wpseo_meta_author', '__return_false');
add_filter('wpseo_opengraph_author_facebook', '__return_false');
