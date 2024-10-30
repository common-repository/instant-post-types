<?php

/**
 * Plugin Name: Instant Post Types
 * Plugin URI: http://pluginscorner.com
 * description: Create custom post types, custom taxonomies and custom fields
 * Version: 0.8.1
 * Author: Plugins Corner
 * Author URI: http://pluginscorner.com/plugins
 * License: GPL2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CPT_PC_PLUGIN', 1 );

require_once( 'classes/class-cpt-pc-constants.php' );
require_once 'classes/class-cpt-pc-autoload.php';

/**
 * Let's start this
 */
new CPT_PC_Autoloader();