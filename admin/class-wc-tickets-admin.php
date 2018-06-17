<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://profglobal.pro/
 * @since      1.0.0
 *
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/admin
 * @author     Vitaliy Karakushan <karakushan@gmail.com>
 */
class Wc_Tickets_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $settings Contains plugin settings.
	 */
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->settings    = new Wc_Tickets_Settings();

		// Add the custom columns
		add_filter( 'manage_wc-tickets_posts_columns', array( $this, 'set_custom_edit_tickets_columns' ) );
		add_action( 'manage_wc-tickets_posts_custom_column', array( $this, 'custom_tickets_column' ), 10, 2 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wc_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wc_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-tickets-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wc_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wc_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-tickets-admin.js', array( 'jquery' ), $this->version, false );

	}


	function set_custom_edit_tickets_columns( $columns ) {
		$new_columns = [];
		$count       = 0;
		if ( count( $columns ) ) {
			foreach ( $columns as $key => $column ) {
				$new_columns[ $key ] = $column;
				if ( $count == 1 ) {
					$new_columns['wct-ititiator'] = __( 'Initiator', 'wc-tickets' );
					$new_columns['wct-messages']  = __( 'Messages', 'wc-tickets' );
				}
				$count ++;
			}
		}

		return $new_columns;
	}

	function custom_tickets_column( $column, $post_id ) {

		$post = get_post( $post_id );
		$user = get_user_by( 'ID', $post->post_author );

		switch ( $column ) {
			case 'wct-ititiator' :
				echo get_avatar( $post->post_author, 60 );
				echo '<div class="user-data">';
				echo '<b>' . $user->first_name . ' ' . $user->last_name . '</b>';
				echo '<span class="user-email">' . $user->user_email . '</span>';
				echo '<a href="' . esc_url( get_edit_user_link( $post->post_author ) ) . '">' . esc_html( $user->data->user_login ) . '</a>';
				echo '</div>';
				break;
			case 'wct-messages':
				$tickets     = get_post_meta( $post_id, '_wc_ticket', 0 );
				$last_ticket = end( $tickets );
				$user_last   = get_user_by( 'ID', intval( $last_ticket['author'] ) );
				echo '<div class="messages-total">' . sprintf( __( '<b>Total:</b> %d', 'wc-tickets' ), count( $tickets ) ) . '</div>';
				echo '<div class="messages-last">' . sprintf( __( '<b>%s writes:</b> %s', 'wc-tickets' ), $user_last->first_name, $last_ticket['message'] ) . '</div>';
				break;

		}
	}


}
