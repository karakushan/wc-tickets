<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://profglobal.pro/
 * @since             1.0.0
 * @package           Wc_Tickets
 *
 * @wordpress-plugin
 * Plugin Name:       WC Tickets
 * Plugin URI:        https://profglobal.pro/download/wc-tickets/
 * Description:       Plugin allows you to create a ticket system for your online store on Woocommerce
 * Version:           1.0.0
 * Author:            Vitaliy Karakushan
 * Author URI:        http://profglobal.pro/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-tickets
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WC_TIСKETS_VER', '1.0.0' );

/**
 * Prefix to functions and hooks.
 */
define( 'WC_TIСKETS_PREFIX', 'wc_tickets_' );

/**
 * The path to the plugin files starting with http: //
 */
define( 'WC_TIСKETS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Server path to the plugin files
 */
define( 'WC_TIСKETS_PATH', plugin_dir_path( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-tickets-activator.php
 */
function activate_wc_tickets() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-tickets-activator.php';
	Wc_Tickets_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-tickets-deactivator.php
 */
function deactivate_wc_tickets() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-tickets-deactivator.php';
	Wc_Tickets_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wc_tickets' );
register_deactivation_hook( __FILE__, 'deactivate_wc_tickets' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-tickets.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_tickets() {

	$plugin = new Wc_Tickets();
	$plugin->run();

}

run_wc_tickets();
