<?php

class Wc_Tickets_Settings {
	/**
	 * A unique menu slug for the settings page.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $menu_slug A unique menu slug for the settings page.
	 */
	protected $menu_slug = 'wc-tickets';

	protected $plugin_options_name;

	protected $plugin_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		$this->plugin_options_name = WC_TIСKETS_PREFIX . 'options';

		$this->plugin_options = get_option( $this->plugin_options_name );
	}

	/**
	 * Returns the value of the option specified in  @param $option_name
	 *
	 * @param string $option_name option key in DB
	 * @param string $default this value is returned if the parameter $ option_name is not found or is empty
   *
	 *
	 * @return string
	 */
	function get_option( $option_name = '', $default = '' ) {
		if ( ! empty( $this->plugin_options[ $option_name ] ) ) {
			$value = $this->plugin_options[ $option_name ];
		} else {
			$value = $default;
		}

		return $value;
	}

	function default_from_email() {
		// Get the site domain and get rid of www.
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}

		$from_email = 'wordpress@' . $sitename;

		return $from_email;
	}


	function add_admin_menu() {

		add_options_page( __( 'WC Tickets', 'wc-tickets' ), __( 'WC Tickets', 'wc-tickets' ), 'manage_options', $this->menu_slug, array(
			$this,
			'options_page'
		) );

	}


	/**
	 * This method initializes the plug-in settings panel
	 */
	function settings_init() {

		register_setting( $this->menu_slug, $this->plugin_options_name );

		add_settings_section(
			WC_TIСKETS_PREFIX . 'base',
			__( 'Basic settings', 'wc-tickets' ),
			array( $this, 'settings_section_callback' ),
			$this->menu_slug
		);

		add_settings_field(
			'working_email',
			__( 'Mail for receiving letters', 'wc-tickets' ),
			array( $this, 'text_field_render' ),
			$this->menu_slug,
			WC_TIСKETS_PREFIX . 'base',
			array(
				'name'    =>  'working_email',
				'default' => get_bloginfo('admin_email'),
			)

		);
		add_settings_field(
			'senders_email',
			__( 'Email sender\'s email', 'wc-tickets' ),
			array( $this, 'text_field_render' ),
			$this->menu_slug,
			WC_TIСKETS_PREFIX . 'base',
			array(
				'name'    =>  'senders_email',
				'default' => $this->default_from_email(),
			)

		);

		add_settings_field(
			'senders_name',
			__( 'Sender\'s name', 'wc-tickets' ),
			array( $this, 'text_field_render' ),
			$this->menu_slug,
			WC_TIСKETS_PREFIX . 'base',
			array(
				'name'    => 'senders_name',
				'default' => get_bloginfo( 'name' )
			)

		);
		add_settings_field(
			'mail_subject',
			__( 'Email subject', 'wc-tickets' ),
			array( $this, 'text_field_render' ),
			$this->menu_slug,
			WC_TIСKETS_PREFIX . 'base',
			array(
				'name'    => 'mail_subject',
				'default' => __( 'Ticket #%d was successfully created.', 'wc-tickets' )
			)

		);
		add_settings_field(
			'text_letter',
			__( 'Text of letter to the user', 'wc-tickets' ),
			array( $this, 'editor_field_render' ),
			$this->menu_slug,
			WC_TIСKETS_PREFIX . 'base',
			array(
				'name'    => 'text_letter',
				'editor'  => array(
					'tinymce' => 0
				),
				'default' => __( 'You initiated the creation of a ticket on the theme "%s". The support service will send a response to this email.', 'wc-tickets' )
			)
		);
	}

	/**
	 * Displays the text field for the settings
	 *
	 * @param array $args
	 */
	function text_field_render( $args = array() ) {
		$value = $this->plugin_options[ $args['name'] ];
		if ( empty( $value ) && ! empty( $args['default'] ) ) {
			$value = $args['default'];
		}
		?>
      <input type='text'
             name='<?php echo esc_attr( $this->plugin_options_name ) ?>[<?php echo esc_attr( $args['name'] ) ?>]'
             value='<?php echo esc_attr( $value ); ?>'>
		<?php

	}

	/**
	 * Displays the Wordress editor field
	 *
	 * @param array $args
	 */
	function editor_field_render( $args = array() ) {
		$value = $this->plugin_options[ $args['name'] ];
		if ( empty( $value ) && ! empty( $args['default'] ) ) {
			$value = $args['default'];
		}
		$name        = $this->plugin_options_name . '[' . $args['name'] . ']';
		$editor_args = wp_parse_args( $args['editor'], array(
			'wpautop'          => 1,
			'media_buttons'    => 1,
			'textarea_name'    => esc_attr( $name ),
			'textarea_rows'    => 10,
			'tabindex'         => null,
			'editor_css'       => '',
			'editor_class'     => '',
			'teeny'            => 0,
			'dfw'              => 0,
			'tinymce'          => 1,
			'quicktags'        => 1,
			'drag_drop_upload' => false
		) );

		wp_editor( $value, esc_attr( '' . $args['name'] ), $editor_args );

	}


	/**
	 *  The text displayed before the beginning of the fields of the section
	 */
	function settings_section_callback() {


	}


	/**
	 *  Displays plugin sections and fields
	 */
	function options_page() {

		?>
      <h1><?php _e( 'WC Tickets Settings', 'wc-tickets' ); ?></h1>
      <form action='options.php' method='post' class="wc-tickets-settings">
		  <?php
		  settings_fields( $this->menu_slug );
		  do_settings_sections( $this->menu_slug );
		  submit_button();
		  ?>
      </form>
		<?php

	}
}