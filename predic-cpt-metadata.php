<?php
/**
 * Plugin Name: Predic CPT metadata
 * Description: The plugin that extends the WP metadata API to support saving the CPT data to custom meta tables
 * Version: 0.1.0
 * Author: Aleksandar Predic
 * Author URI: https://acapredic.com/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

use PredicCptMetadata\PluginInit;

//  Exit if accessed directly.
defined('ABSPATH') || exit;

define('PREDIC_CPT_METADATA_PLUGIN_FILE', __FILE__);
define('PREDIC_CPT_METADATA_DIR', plugin_dir_path(__FILE__));
define('PREDIC_CPT_METADATA_DEBUG_ENABLED', defined('WP_DEBUG_LOG') && WP_DEBUG_LOG);

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action('plugins_loaded', fn() => PluginInit::getInstance()->setInstances(), 20);
