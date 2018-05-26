<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://profglobal.pro/
 * @since      1.0.0
 *
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/includes
 * @author     Vitaliy Karakushan <karakushan@gmail.com>
 */
class Wc_Tickets_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		add_rewrite_endpoint( 'tickets', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

}
