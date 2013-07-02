<?php
/**
 * Community Watch
 *
 * Allows users to report inappropriate content across all a site's content types.
 *
 * @package   CommunityWatch
 * @author    Josh Eaton <josh@josheaton.org>
 * @license   GPL-2.0+
 * @link      http://www.josheaton.org/
 * @copyright 2013 Josh Eaton
 *
 * @wordpress-plugin
 * Plugin Name: Community Watch
 * Plugin URI:  http://www.josheaton.org/
 * Description: Allows users to report inappropriate content across all content types.
 * Version:     1.0.0
 * Author:      Josh Eaton
 * Author URI:  http://www.josheaton.org/
 * Text Domain: community-watch
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include the plugin class
require_once( plugin_dir_path( __FILE__ ) . 'class-community-watch.php' );

// Register activation and deactivation hooks
register_activation_hook( __FILE__, array( 'CommunityWatch', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CommunityWatch', 'deactivate' ) );

// Get the class instance
CommunityWatch::get_instance();
