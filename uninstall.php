<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   CommunityWatch
 * @author    Josh Eaton <josh@josheaton.org>
 * @license   GPL-2.0+
 * @link      http://www.josheaton.org/
 * @copyright 2013 Josh Eaton
 */

// If uninstall, not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Include the plugin class and fire up our plugin
require_once( plugin_dir_path( __FILE__ ) . 'class-community-watch.php' );
$cw = CommunityWatch::get_instance();

// Delete the options
delete_option( 'cw_display' );

// Delete the posts
global $wpdb;
$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type = %s", $cw::post_type ) );
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '\_cw\_reported\_%'" );
// delete bot user
require_once(ABSPATH . 'wp-admin/includes/user.php');
wp_delete_user( get_bot()->ID );
