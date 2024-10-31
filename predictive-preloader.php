<?php
/**
 * Plugin Name: Predictive Preloader
 * Description: We Predict User Flow And Preload Pages to Increase Page Speed.
 * Version: 1.3
 * Author: Sam Heaton
 * Author URI: https://predictivepreloader.com
 */
require_once( plugin_dir_path( __FILE__ ) . 'front/class-preloader-front.php' );
/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
	
register_activation_hook( __FILE__, array( 'Preloader_Front', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Preloader_Front', 'deactivate' ) );

/*
 * Get instance
 */
add_action( 'plugins_loaded', array( 'Preloader_Front', 'get_instance' ) );

if ( is_admin()) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-preloader-admin.php' );

	/*
	 * Get instance
	 */
	add_action( 'plugins_loaded', array( 'Preloader_Admin', 'get_instance' ) );
}