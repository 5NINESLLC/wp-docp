<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://design.garden
 * @since             1.0.0
 * @package           Docc
 *
 * @wordpress-plugin
 * Plugin Name:       DOCC app
 * Plugin URI:        https://design.garden
 * Description:       Main plugin for custom app functionality.
 * Version:           3.0.2
 * Author:            design.garden
 * Author URI:        https://design.garden
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       docc
 * Domain Path:       /languages
 */

// register_shutdown_function( "docc_fatal_handler" ); function docc_fatal_handler() { var_dump(error_get_last()); }

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DOCC_VERSION', '3.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-docc-activator.php
 */
function activate_docc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-docc-activator.php';
	Docc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-docc-deactivator.php
 */
function deactivate_docc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-docc-deactivator.php';
	Docc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_docc' );
register_deactivation_hook( __FILE__, 'deactivate_docc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-docc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_docc() {

	$plugin = new Docc();
	$plugin->run();

}
run_docc();
