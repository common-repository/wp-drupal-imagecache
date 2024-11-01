<?php
/**
 * Plugin Name: Thumbnails like in Drupal
 * Description: Wordpress implementation/fork of Drupal Imagecache module.
 * Plugin URI:  https://web-marshal.ru/wordpress-thumbnails-like-in-drupal/
 * Author URI:  https://www.linkedin.com/in/stasionok/
 * Author:      Stanislav Kuznetsov
 * Version:     1.1.2
 * License: GPLv2 or later
 * Text Domain: wp-drupal-imagecache
 * Domain Path: /languages
 *
 * Network: false
 */

defined( 'ABSPATH' ) || exit;

define( 'WPDI_REQUIRED_PHP_VERSION', '7.0' ); // tested only from 7.0
define( 'WPDI_REQUIRED_WP_VERSION', '5.3' ); // because of attachment_id for intermediate_image_sizes_advanced added only from 5.3

/**
 * Checks if the system requirements are met
 *
 * @return bool|array Array of errors or false if all is ok
 */
function wpdi_requirements_met() {
	global $wp_version;

	$errors = false;

	$is_ext_loaded = extension_loaded( 'gd' ) && function_exists( 'gd_info' );
	if ( ! $is_ext_loaded && ! extension_loaded( 'imagick' ) ) {
		$errors[] = __( "There is no GD or Imagick extension loaded in your php. Please load one of them.", WPDI_Common::PLUGIN_SYSTEM_NAME );
	}


	if ( version_compare( PHP_VERSION, WPDI_REQUIRED_PHP_VERSION, '<' ) ) {
		$errors[] = __( "Your server is running PHP version " . PHP_VERSION . " but this plugin requires at least PHP " . WPDI_REQUIRED_PHP_VERSION . ". Please run an upgrade.", WPDI_Common::PLUGIN_SYSTEM_NAME );
	}

	if ( version_compare( $wp_version, WPDI_REQUIRED_WP_VERSION, '<' ) ) {
		$errors[] = __( "Your Wordpress running version is " . esc_html( $wp_version ) . " but this plugin requires at least version " . WPDI_REQUIRED_WP_VERSION . ". Please run an upgrade.", WPDI_Common::PLUGIN_SYSTEM_NAME );
	}

	return $errors;
}

/**
 * Begins execution of the plugin.
 *
 * Plugin run entry point
 */
function wpdi_run_wp_drupal_imagecache() {
	$plugin = new WPDI_Common();
	$plugin->run();
}


/**
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met.
 * Otherwise older PHP installations could crash when trying to parse it.
 */
require_once( __DIR__ . '/controller/class-wpdi-common.php' );

$errors = wpdi_requirements_met();
if ( ! $errors ) {
	if ( method_exists( WPDI_Common::class, 'activate' ) ) {
		register_activation_hook( __FILE__, array( WPDI_Common::class, 'activate' ) );
	}

	// to prevent "header already sent" in wp_redirect
	add_action( 'init', function () {
		$stat = ob_get_status();
		if ( $stat['level'] == 0 ) {
			ob_start();
		}
	} );

	wpdi_run_wp_drupal_imagecache();
} else {
	add_action( 'admin_notices', function () use ( $errors ) {
		require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
	} );
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( '/wp-drupal-imagecache/bootstrap.php' );
}

if ( method_exists( WPDI_Common::class, 'deactivate' ) ) {
	register_deactivation_hook( __FILE__, array( WPDI_Common::class, 'deactivate' ) );
}

if ( method_exists( WPDI_Common::class, 'uninstall' ) ) {
	register_uninstall_hook( __FILE__, array( WPDI_Common::class, 'uninstall' ) );
}

