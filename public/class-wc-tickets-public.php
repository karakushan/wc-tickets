<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://profglobal.pro/
 * @since      1.0.0
 *
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/public
 * @author     Vitaliy Karakushan <karakushan@gmail.com>
 */
class Wc_Tickets_Public {

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


	private $post_type = 'wc-tickets';

	private $query_var = 'tickets';


	protected $nonce = 'wc-tickets';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// отправка тикета
		add_action( 'wp_ajax_send_ticket', array( $this, 'send_ticket_callback' ) );
		add_action( 'wp_ajax_nopriv_send_ticket', array( $this, 'send_ticket_callback' ) );

		// подключаем шаблон формы тикетов
		add_action( 'woocommerce_account_tickets_endpoint', array( $this, 'tickets_endpoint_content' ) );

		//	Add endpoint
		add_action( 'init', array( $this, 'add_my_account_endpoint' ) );

		// Account menu items
		add_filter( 'woocommerce_account_menu_items', array( $this, 'account_menu_items', 10, 1 ) );

		// register "wc-tickets" post type
		add_action( 'init', array( $this, 'register_post_types' ) );

		add_filter( 'query_vars', array( $this, 'tickets_query_vars' ), 0 );

		// Registers metabox
		add_action( 'add_meta_boxes', array( $this, 'wc_tickets_add_custom_box' ) );

		// saving the metabox data
		add_action( 'save_post', array( $this, 'save_tickets' ) );

		// register custom plugin post status
		add_action( 'init', array( $this, 'custom_ticket_status' ) );

		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );

		// Ticket closing
		add_action( 'wp_ajax_wct_close_ticket', array( $this, 'wct_close_ticket_callback' ) );
		add_action( 'wp_ajax_nopriv_wct_close_ticket', array( $this, 'wct_close_ticket_callback' ) );

		if ( ! is_admin() ) {
			add_filter( 'the_editor', array( $this, 'add_required_attribute_to_wp_editor' ), 10, 1 );
		}

	}


	function add_required_attribute_to_wp_editor( $editor ) {
		$editor = str_replace( '<textarea', '<textarea required="required"', $editor );

		return $editor;
	}


	/**
	 * Ticket closing
	 */
	function wct_close_ticket_callback() {
		if ( ! wp_verify_nonce( $_POST['nonce'] ) ) {
			echo json_encode( array(
				'status' => 0
			) );
			wp_die();
		}
		$post_id = 0;
		if ( ! empty( $_POST['nonce'] ) ) {
			$post_id = wp_update_post( array(
				'ID'          => intval( $_POST['ticket'] ),
				'post_status' => 'closed'
			) );
		}

		if ( $post_id ) {
			echo json_encode( array(
				'status'  => 1,
				'message' => __( 'Closed', 'wc-tickets' )
			) );
			wp_die();
		} else {
			echo json_encode( array(
				'status' => 0
			) );
			wp_die();
		}

		echo json_encode( $_POST );
		exit();
	}

	function display_post_states( $post_states, $post ) {
		if ( $post->post_type === $this->post_type && $post->post_status == 'closed' ) {
			$post_states[] = __( 'Closed', 'wc-tickets' );
		}

		return $post_states;
	}

	function custom_ticket_status() {
		register_post_status( 'closed', array(
			'label'                     => _x( 'Closed', $this->post_type, 'wc-tickets' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'wc-tickets' ),
		) );
	}


	/**
	 * Registers metabox
	 */
	function wc_tickets_add_custom_box() {
		add_meta_box( 'wc_tickets', __( 'Ticket Messages', 'wc-tickets' ), array(
			$this,
			'wc_tickets_metabox_template'
		), array( $this->post_type ) );
	}

	/**
	 * Makes the message panel template
	 */
	function wc_tickets_metabox_template() {
		global $post, $wpdb;

		$tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id='%s' AND meta_key='%s' ORDER BY meta_id DESC ", $post->ID, '_wc_ticket' ) );

		//We use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), $this->nonce );
		add_thickbox();

		if ( file_exists( WC_TIСKETS_PATH . "public/partials/ticket-metabox.php" ) ) {
			include WC_TIСKETS_PATH . "public/partials/ticket-metabox.php";
		}

	}

	/**
	 * Register "wc-tickets" post type
	 */
	function register_post_types() {
		register_post_type( $this->post_type, array(
			'label'               => __( 'Tickets', 'wc-tickets' ),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-format-chat',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			// 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
			'taxonomies'          => array(),
			'has_archive'         => false,
			'rewrite'             => true,
			'query_var'           => true,
		) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		wp_enqueue_style( 'thickbox.css', includes_url( '/js/thickbox/thickbox.css' ), null );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-tickets-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( 'thickbox', null, array( 'jquery' ), true );


		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-tickets-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'wcTickets',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);

	}

	/**
	 * Inserts a ticket into the database
	 *
	 * @param $post_id
	 * @param array $data
	 */
	function insert_ticket( $post_id, $data = [] ) {
		$data['author'] = get_current_user_id();
		$data['date']   = time();
		if ( $data ) {
			add_post_meta( $post_id, '_wc_ticket', $data );
		}
	}


	/**
	 * Sends a ticket from the user
	 *
	 */
	function send_ticket_callback() {
		// check where the Ajax request was sent from
		if ( ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
			echo json_encode(
				array(
					'status'  => 0,
					'message' => __( 'The request did not pass the security check', 'wc-tickets' )
				)
			);
			wp_die();
		}

		// Upload photo
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$file       = &$_FILES['file'];
		$new_ticket = []; // array of ticket
		$overrides  = array( 'test_form' => false );
		if ( ! empty( $_FILES['file'] ) ) {
			$movefile = wp_handle_upload( $file, $overrides );
		} else {
			$movefile = [];
		}

		$current_user   = wp_get_current_user();
		$ticket_title   = wp_strip_all_tags( $_POST['subject'] );
		$ticket_message = $_POST['ticket-editor'];
		$settings_class = new Wc_Tickets_Settings();
		$admin_email    = $settings_class->get_option( 'working_email', get_bloginfo( 'admin_email' ) );

		// ticket data
		$post_data = array(
			'post_title'   => $ticket_title,
			'post_content' => $ticket_message,
			'post_status'  => 'publish',
			'post_author'  => $current_user->ID,
			'post_type'    => $this->post_type,
		);

		// if the parent is specified, then we update the ticket
		if ( ! empty( $_POST['parent'] ) && is_numeric( $_POST['parent'] ) ) {
			$post_data['ID']         = intval( $_POST['parent'] );
			$post_data['post_title'] = get_the_title( intval( $_POST['parent'] ) );
		}

		if ( ! empty( $_POST['user_name'] ) ) {
			update_user_meta( $current_user->ID, 'first_name', sanitize_text_field( $_POST['user_name'] ) );
		}

		$post_id = wp_insert_post( $post_data );

		if ( ! is_wp_error( $post_id ) ) {
			$new_ticket['message'] = $ticket_message;

			if ( ! empty( $movefile ) ) {
				$attachment = array(
					'guid'           => basename( $movefile['url'] ),
					'post_mime_type' => $movefile['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $movefile['url'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				$attach_id = wp_insert_attachment( $attachment, $movefile['url'], $post_id );
				if ( $attach_id ) {
					$new_ticket['image'] = $attach_id;
				}
			}

			if ( ! empty( $new_ticket ) && is_array( $new_ticket ) ) {
				$this->insert_ticket( $post_id, $new_ticket );
			}

			$settings     = new Wc_Tickets_Settings();
			$mail_headers = [];
			// Email headers from
			$mail_headers[] = sprintf( 'From: %s <%s>',
				$settings->get_option( 'senders_name', get_bloginfo( 'name' ) ),
				$settings->get_option( 'senders_email', $settings->default_from_email() )
			);
			// Enable HTML support in the email
			$mail_headers[] = 'content-type: text/html';

			if ( ! empty( $_POST['parent'] ) && is_numeric( $_POST['parent'] ) ) {
				// Sending a message to the admin
				$subject_admin = sprintf( __( 'Ticket #%d - "%s". User message.', 'wc-tickets' ), $post_id, $post_data['post_title'] );
				$message_admin = sprintf( '<p>' . __( 'User: "%s"', 'wc-tickets' ), $current_user->user_login ) . '</p>';
				$message_admin .= sprintf( '<p>' . __( 'First Name Last Name: "%s"', 'wc-tickets' ), $current_user->first_name . ' ' . $current_user->last_name ) . '</p>';
				$message_admin .= sprintf( '<p>' . __( 'Message: "%s"', 'wc-tickets' ), $ticket_message ) . '</p>';
				wp_mail( $admin_email, $subject_admin, $message_admin, $mail_headers );
			} else {
				// Sending a message to the user
				$subject_user = sprintf( $settings->get_option( 'mail_subject', __( 'Ticket #%d was successfully created.', 'wc-tickets' ) ), $post_id );
				$message_user = sprintf( $settings->get_option( 'text_letter', __( 'You initiated the creation of a ticket on the theme "%s". The support service will send a response to this email.', 'wc-tickets' ) ), $ticket_title );
				wp_mail( $current_user->user_email, $subject_user, $message_user, $mail_headers );

				// Sending a message to the admin
				$subject_admin = sprintf( __( 'User %s created a ticket #%d ', 'wc-tickets' ), $current_user->user_login, $post_id );
				$message_admin = sprintf( __( '<h4>' . 'Subject: "%s"', 'wc-tickets' ), $ticket_title ) . '</h4>';
				$message_admin .= sprintf( '<p>' . __( 'User: "%s"', 'wc-tickets' ), $current_user->user_login ) . '</p>';
				$message_admin .= sprintf( '<p>' . __( 'First Name Last Name: "%s"', 'wc-tickets' ), $current_user->first_name . ' ' . $current_user->last_name ) . '</p>';
				$message_admin .= sprintf( '<p>' . __( 'Message: "%s"', 'wc-tickets' ), $ticket_message ) . '</p>';
				wp_mail( $admin_email, $subject_admin, $message_admin, $mail_headers );
			}


			echo json_encode(
				array(
					'status'  => 1,
					'message' => sprintf( __( 'Ticket #%d was successfully created.', 'wc-tickets' ), $post_id )
				)
			);
		}

		wp_die();
	}

	/**
	 * Here we connect the ticket form template
	 */
	function tickets_endpoint_content() {
		$tickets_template = WC_TIСKETS_PATH . 'public/partials/tickets-form.php';
		if ( file_exists( $tickets_template ) ) {
			$current_user = wp_get_current_user();
			include $tickets_template;
		}
	}

	/**
	 * Add endpoint
	 */
	function add_my_account_endpoint() {
		add_rewrite_endpoint( $this->query_var, EP_ROOT | EP_PAGES );
	}


	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	function tickets_query_vars( $vars ) {
		$vars[] = $this->query_var;

		return $vars;
	}


	/**
	 * Account menu items
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	function account_menu_items( $items ) {
		$items[ $this->query_var ] = __( 'Complaints and suggestions', 'wc-tickets' );

		return $items;
	}

	/**
	 * Save the data when the post is saved
	 *
	 * @param $post_id
	 */
	function save_tickets( $post_id ) {
		// проверяем nonce нашей страницы, потому что save_post может быть вызван с другого места.
		if ( ! wp_verify_nonce( $_POST[ $this->nonce ], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// если это автосохранение ничего не делаем
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// проверяем права юзера
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		global $post;
		if ( ! empty( $_POST['wc-tickets-reply'] ) ) {
			$ticket_data['message'] = $_POST['wc-tickets-reply'];

			$this->insert_ticket( $post_id, $ticket_data );
			$settings     = new Wc_Tickets_Settings();
			$mail_headers = [];
			// Email headers from
			$mail_headers[] = sprintf( 'From: %s <%s>',
				$settings->get_option( 'senders_name', get_bloginfo( 'name' ) ),
				$settings->get_option( 'senders_email', $settings->default_from_email() )
			);
			// Enable HTML support in the email
			$mail_headers[] = 'content-type: text/html';

			// Sending a message to the user
			$subject_user = sprintf( $settings->get_option( 'mail_subject_second', __( 'Ticket #%d - "%s". Support Response.', 'wc-tickets' ) ), $post_id, $post->post_title );
			$message_user = $_POST['wc-tickets-reply'];
			$user         = get_user_by( 'ID', $post->post_author );
			wp_mail( $user->user_email, $subject_user, $message_user, $mail_headers );
		}
		// Change the status of the post if ticked
		if ( ! empty( $_POST['wc-tickets-close'] ) && $_POST['wc-tickets-close'] == '1' ) {
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'closed'
			) );
		}

	}
}
