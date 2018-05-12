<?php
/**
 * @package Akismet
 */
/*
Plugin Name: JL Custom Permalinks Plugin
Plugin URI: https://github.com/jluispcardenas/jl-custom-permalinks
Description: This plugin allows you to create many permalinks for each post.
Version: 0.1.1
Author: JL Cardenas
License: GPLv2 or later
Text Domain: jpc
*/

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'JCP_VERSION', '4.0.2' );
define( 'JCP__MINIMUM_WP_VERSION', '4.0' );
define( 'JCP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'Jcp', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Jcp', 'plugin_deactivation' ) );

require_once( JCP__PLUGIN_DIR . 'class.jcp.php' );

add_action( 'init', array( 'Jcp', 'init' ), 10, 0 );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once( JCP__PLUGIN_DIR . 'class.jcp-admin.php' );
	add_action( 'init', array( 'Jcp_Admin', 'init' ) );
}
