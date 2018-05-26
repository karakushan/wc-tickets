<?php

/**
 * Fired during plugin activation
 *
 * @link       http://profglobal.pro/
 * @since      1.0.0
 *
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/includes
 * @author     Vitaliy Karakushan <karakushan@gmail.com>
 */
class Wc_Tickets_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_rewrite_endpoint( 'tickets', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

}
