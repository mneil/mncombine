<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package MnCombine
 * @author  Your Name <email@example.com>
 * @license GPL-2.0+
 * @link    http://mneilsworld.com/mncombine
 *
 * @wordpress-plugin
 * Plugin Name: MnCombine
 * Plugin URI: http://mneilsworld.com/mncombine
 * Description: Easily manage the merging and compression of js and css files from plugins and themes
 * Version: 1.0.3
 * Author: Michael Neil
 * Author URI: http://mneilsworld.com/
 * Author Email: mneil@mneilsworld.com
 * Text Domain: mn-combine
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-plugin-mncombine.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'MnCombine', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MnCombine', 'deactivate' ) );

MnCombine::get_instance();