<?php
/**
 * GravityZWR Feed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Include the addon framework
 */
GFForms::include_feed_addon_framework();


/**
 * GravityZWR Class is an extension of the Gravity Forms Add-On Class.
 *
 * @uses GFAddOn::log_error()
 * @uses GFAddOn::log_debug()
 * @uses GFAddOn::get_plugin_settings()
 * @uses GFCache::delete()
 */
class GravityZWR extends GFFeedAddOn {

	/**
	 * Plugin Version
	 *
	 * @var string $_version
	 */
	protected $_version = GRAVITYZWR_VERSION;

	/**
	 * Minimum required version of Gravity Forms
	 *
	 * @var string $_min_gravityforms_version
	 */
	protected $_min_gravityforms_version = '2.2';

	/**
	 * Plugin Slug
	 *
	 * @var string $_slug
	 */
	protected $_slug = 'gravity-forms-zoom-webinar-registration';

	/**
	 * Plugin Path
	 *
	 * @var string $_path
	 */
	protected $_path = 'gravity-forms-zoom-webinar-registration/gravity-forms-zoom-webinar-registration.php';

	/**
	 * Plugin Full Path
	 *
	 * @var [type]
	 */
	protected $_full_path = __FILE__;

	/**
	 * Title of Add-On
	 *
	 * @var string $_title
	 */
	protected $_title = 'Gravity Forms Zoom Webinar Registration';

	/**
	 * Short Title of Add-On
	 *
	 * @var string $_short_title
	 */
	protected $_short_title = 'Zoom Webinar';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = false;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_zoomwr';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_zoomwr';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_zoomwr_uninstall';

	/**
	 * Defines the capabilities needed for the Post Creation Add-On
	 *
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_zoomwr', 'gravityforms_zoomwr_uninstall' );

	/**
	 * Core singleton class
	 *
	 * @var self - pattern realization
	 */
	private static $_instance;

	/**
	 * Get an instance of this class.
	 *
	 * @return GravityZWR
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new GravityZWR();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Register contact to Zoom Webinar only when payment is received.', 'gravity-zwr' ),
			)
		);

		$plugin_settings = GFCache::get( 'zwr_plugin_settings' );
		if ( empty( $plugin_settings ) ) {
			$plugin_settings = $this->get_plugin_settings();
			GFCache::set( 'zwr_plugin_settings', $plugin_settings );
		}
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {
		return array_merge( parent::styles(), [
			[
				'handle'  => 'gravityzwr_join_url_support',
				'src'     => GRAVITYZWR_PLUGIN_DIR . 'includes/css/entry-details.css',
				'version' => time(), // TODO: $this->_version
				'enqueue' => [ [ 'query' => 'page=gf_entries' ] ],
			]
		]);
	} // End styles()

	/**
	 * Form settings icon
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return 'dashicons-video-alt2 dashicons';
	} // End get_menu_icon()

	/**
	 * Remove unneeded settings.
	 */
	public function uninstall() {

		parent::uninstall();

		GFCache::delete( 'zwr_plugin_settings' );
	}

	/**
	 * Prevent feeds being listed or created if an api key isn't valid.
	 *
	 * @return bool
	 */
	public function can_create_feed() {

		// Get the plugin settings.
		$settings = $this->get_plugin_settings();

		// New OAuth settings fields. Check if any of the settings fields are defined as constants first.
		$account = defined( 'GRAVITYZWR_ACCOUNT_ID' ) ? GRAVITYZWR_ACCOUNT_ID : rgar( $settings, 'zoomaccountid' );
		$client  = defined( 'GRAVITYZWR_CLIENT_ID' ) ? GRAVITYZWR_CLIENT_ID : rgar( $settings, 'zoomclientid' );
		$secret  = defined( 'GRAVITYZWR_CLIENT_SECRET' ) ? GRAVITYZWR_CLIENT_SECRET : rgar( $settings, 'zoomclientsecret' );

		// If any settings fields are blank, return false.
		if ( empty( $account ) || empty( $client ) || empty( $secret ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return array(
			'feedName'      => esc_html__( 'Feed Name', 'gravity-zwr' ),
			'zoomWebinarID' => esc_html__( 'Meeting ID', 'gravity-zwr' ),
		);

	}

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		$description = '<p>' .
			sprintf(
				/* translators: %1$s - opening <a> tag, %2$s - closing <a> tag */
				esc_html__( 'Zoom Webinars make it easy to host your virtual events. Use Gravity Forms to create new attendees in your webinars, without the need of 3rd party services. You will need to %1$screate your own Server OAuth private app%2$s to make this add-on work.', 'gravity-zwr' ),
				'<a href="https://marketplace.zoom.us/docs/guides/build/server-to-server-oauth-app/" target="_blank">', '</a>'
			)
			. '</p>';

		// If settings constants are define, add a notice to the settings page.
		if ( defined( 'GRAVITYZWR_ACCOUNT_ID' ) || defined( 'GRAVITYZWR_CLIENT_ID' ) || defined( 'GRAVITYZWR_CLIENT_SECRET' ) ) {
			$description .= '<div class="notice notice-warning inline"><p>' .
				sprintf(
					/* translators: %1$s - opening <code> tag, %2$s - closing <code> tag */
					esc_html__( 'You have defined one or more of the following constants in your wp-config.php file: %1$sGRAVITYZWR_ACCOUNT_ID%2$s, %1$sGRAVITYZWR_CLIENT_ID%2$s, %1$sGRAVITYZWR_CLIENT_SECRET%2$s. These constants will override the settings below.', 'gravity-zwr' ),
					'<code>', '</code>'
				)
				. '</p></div>';
		}
		return array(
			array(
				'title'       => esc_html__( 'Zoom Webinar Settings', 'gravity-zwr' ),
				'description' => $description,
				'fields'      => array(
					array(
						'name'              => 'zoomaccountid',
						'tooltip'           => esc_html__( 'This is the Account ID provided in your Server-to-Server OAuth app.', 'gravity-zwr' ),
						'label'             => esc_html__( 'Zoom Account ID', 'gravity-zwr' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'name'              => 'zoomclientid',
						'tooltip'           => esc_html__( 'This is the Client ID provided in your Server-to-Server OAuth app.', 'gravity-zwr' ),
						'label'             => esc_html__( 'Zoom Client ID', 'gravity-zwr' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'name'              => 'zoomclientsecret',
						'tooltip'           => esc_html__( 'This is the Client Secret provided in your Server-to-Server OAuth app.', 'gravity-zwr' ),
						'label'             => esc_html__( 'Zoom Client Secret', 'gravity-zwr' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					[
						'label'   => esc_html__( 'Default Meeting Type', 'gravity-zwr' ),
						'type'    => 'select',
						'name'    => 'zoomdefaultmeetingtype',
						'tooltip' => esc_html__( 'While created for webinars, this plugin will also work for normal meetings. You may change which one you would like to be selected by default. You can also change it on the individual feeds.', 'gravity-zwr' ),
						'choices' => [
							[
								'label' => esc_html__( 'Webinar', 'gravity-zwr' ),
								'value' => 'webinars',
							],
							[
								'label' => esc_html__( 'Meeting', 'gravity-zwr' ),
								'value' => 'meetings',
							],
						],
					],
					[
                        'type' => 'html',
                        'name' => 'zoomformjson',
                        'args' => [
                            'html' => '<br><br><strong>Optional:</strong> '.wp_kses(
								/* translators: %s - link to the json form file */
								sprintf( __( 'Save and import the %s file as a starter form (right-click + save link as). All required and optional registration fields are included.', 'gravity-zwr' ), 
								'<code><a href="'.esc_url( GRAVITYZWR_URI ).'gravity-forms-zoom-registration-sample-form.json">gravity-forms-zoom-registration-sample-form.json</a></code>' 
								), [ 'code' => [], 'a' => [ 'href' => [] ] ] )
                        ],
                    ]
				),
			),
		);
	}

	/**
	 * Configures the settings which should be rendered on the Form Settings > Zoom Webinar tab.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		$plugin_settings = $this->get_plugin_settings();
		if ( $plugin_settings && isset( $plugin_settings[ 'zoomdefaultmeetingtype' ] ) ) {
			$default_meeting_type = sanitize_key( $plugin_settings[ 'zoomdefaultmeetingtype' ] );
		} else {
			$default_meeting_type = 'webinars';
		}
		
		return array(
			array(
				'title'  => esc_html__( 'Zoom Webinar Settings', 'gravity-zwr' ),
				'fields' => array(
					array(
						'label'         => esc_html__( 'Meeting Type', 'gravity-zwr' ),
						'type'          => 'select',
						'name'          => 'meetingtype',
						'tooltip'       => esc_html__( 'While created for webinars, this feed will also work for normal meetings', 'gravity-zwr' ),
						'default_value' => $default_meeting_type,
						'choices'       => array(
							array(
								'label' => esc_html__( 'Webinar', 'gravity-zwr' ),
								'value' => 'webinars',
							),
							array(
								'label' => esc_html__( 'Meeting', 'gravity-zwr' ),
								'value' => 'meetings',
							),
						),
					),
					array(
						'name'     => 'feedName',
						'label'    => esc_html__( 'Name', 'gravity-zwr' ),
						'type'     => 'text',
						'required' => true,
						'class'    => 'medium',
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Name', 'gravity-zwr' ),
							esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravity-zwr' )
						),
					),
					array(
						'name'     => 'zoomWebinarID',
						'label'    => esc_html__( 'Webinar/Meeting ID', 'gravity-zwr' ),
						'type'     => 'text',
						'required' => true,
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Webinar/Meeting ID', 'gravity-zwr' ),
							esc_html__( 'Add the Webinar or Meeting ID. You will find this in your Zoom.us webinar or meeting setup.', 'gravity-zwr' )
						),
					),
				),
			),
			array(
				'title'  => esc_html__( 'Registration Fields', 'gravity-zwr' ),
				'fields' => array(
					array(
						'name'      => 'mappedFields',
						'label'     => esc_html__( 'Match fields', 'gravity-zwr' ),
						'type'      => 'field_map',
						'field_map' => $this->merge_vars_field_map(),
						'tooltip'   => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Map Fields', 'gravity-zwr' ),
							esc_html__( 'Setup the Zoom Webinar Registration fields by selecting the appropriate form field from the list.', 'gravity-zwr' )
						),
					),
				),
			),
			array(
				'title'  => 'Feed Conditions',
				'fields' => array(
					array(
						'name'           => 'condition',
						'label'          => esc_html__( 'Condition', 'gravity-zwr' ),
						'type'           => 'feed_condition',
						'checkbox_label' => esc_html__( 'Enable Condition', 'gravity-zwr' ),
						'instructions'   => esc_html__( 'Process this feed if', 'gravity-zwr' ),
					),
				),
			),
		);
	}

	/**
	 * Return an array of Zoom Webinar list fields which can be mapped to the Form fields/entry meta.
	 *
	 * @return array
	 */
	public function merge_vars_field_map() {

		// Initialize field map array.
		$field_map = array();

		// Get merge fields.
		$merge_fields = $this->get_list_merge_fields();

		// If merge fields exist, add to field map.
		if ( ! empty( $merge_fields ) && is_array( $merge_fields ) ) {

			// Loop through merge fields.
			foreach ( $merge_fields as $field => $config ) {

				// Define required field type.
				$field_type = null;

				switch ( strtolower( $config['type'] ) ) {
					case 'name':
						$field_type = array( 'name', 'text' );
						break;

					case 'email':
						$field_type = array( 'email' );
						break;

					case 'phone':
						$field_type = array( 'phone', 'text' );
						break;

					case 'select':
						$field_type = array( 'select', 'radio' );
						break;

					case 'text':
						$field_type = array( 'textarea' );
						break;

					case 'address':
						$field_type = array( 'address', 'text', 'select' );
						break;

					default:
						$field_type = array( 'text', 'hidden' );
						break;
				}

				// Add to field map.
				$field_map[ $field ] = array(
					'name'       => $field,
					'label'      => $config['name'],
					'required'   => $config['required'],
					'field_type' => $field_type,
					// 'tooltip'	 => $config['description'],
				);
			}
		}

		return $field_map;
	}

	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Process the feed: register user with Zoom Webinar.
	 *
	 * @param array $feed  The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form  The form object currently being processed.
	 *
	 * @return array
	 */
	public function process_feed( $feed, $entry, $form ) {

		// Log that we are processing feed.
		$this->log_debug( __METHOD__ . '(): Processing feed.' );

		$meetingtype = in_array( $feed['meta']['meetingtype'], [ 'webinars', 'meetings' ], true ) ? $feed['meta']['meetingtype'] : 'webinars';
		$meeting_id = preg_replace( '/[^0-9]/', '', $feed['meta']['zoomWebinarID'] );

		if ( empty( $meeting_id ) ) {
			$this->add_feed_error( esc_html__( 'Aborted: Empty Webinar/Meeting ID', 'gravity-zwr' ), $feed, $entry, $form );
			return $entry;
		}

		// Check if OAuth settings fields are defined in constants.
		if ( ! defined( 'GRAVITYZWR_ACCOUNT_ID' ) && ! defined( 'GRAVITYZWR_CLIENT_ID' ) && ! defined( 'GRAVITYZWR_CLIENT_SECRET' ) ) {
			$settings = $this->get_plugin_settings();
			if ( empty( $settings ) ) {
				$this->add_feed_error( esc_html__( 'Aborted: Empty Plugin Settings', 'gravity-zwr' ), $feed, $entry, $form );
				return $entry;
			}
		}

		// Retrieve the name => value pairs for all fields mapped in the 'mappedFields' field map.
		$field_map = $this->get_field_map_fields( $feed, 'mappedFields' );

		// Retrieve the labels if any custom fields have been added
		$merge_fields = $this->get_list_merge_fields();

		// Loop through the fields from the field map setting building an array of values to be passed to the third-party service.
		$merge_vars = array();
		$custom_questions = array();
		$join_url_field_id = 0;
		foreach ( $field_map as $name => $field_id ) {

			// If no field is mapped, skip it.
			if ( rgblank( $field_id ) ) {
				continue;
			}

			// Let's skip the join link for now
			if ( $name == 'join_url' ) {
				$join_url_field_id = $field_id;
				continue;
			}

			// Get field value.
			$field_value = $this->get_field_value( $form, $entry, $field_id );

			// If field value is empty, skip it.
			if ( empty( $field_value ) ) {
				continue;
			}

			// Country codes
			if ( $name == 'country' ) {
				$field_value = $this->convert_country_to_iso3166( $field_value );
			}

			// Get the field value for the specified field id
			if ( in_array( $name, $this->get_merge_field_default_keys() ) ) {
				$merge_vars[ $name ] = $field_value;
			} else {
				$custom_questions[] = [
					'title' => $merge_fields[ $name ][ 'name' ],
					'value' => $field_value
				];
			}
		}

		// Only add custom_questions if there are any
		if ( ! empty( $custom_questions ) ) {
			$merge_vars[ 'custom_questions' ] = $custom_questions;
		}

		if ( empty( $merge_vars ) ) {
			$this->add_feed_error( esc_html__( 'Aborted: Empty merge fields', 'gravity-zwr' ), $feed, $entry, $form );
			return $entry;
		}

		// Ensure required fields are present
		if ( ! isset( $merge_vars['email'] ) || ! isset( $merge_vars['first_name'] ) ) {
			$this->add_feed_error( esc_html__( 'Aborted: Missing required fields (email, first_name)', 'gravity-zwr' ), $feed, $entry, $form );
			return $entry;
		}

		// Construct the API endpoint URL
		$api_endpoint = GRAVITYZWR_ZOOMAPIURL . '/' . $meetingtype . '/' . $meeting_id . '/registrants';

		$remote_request = new GravityZWR_ZOOMAPI( $api_endpoint, array( 'body' => wp_json_encode( $merge_vars ) ), 'post' );
		$remote_request->run();

		if ( ! $remote_request->is_success() ) {
			
			// Get the body of the response.
			$response_body = $remote_request->get_body();

			// Decode the JSON response to access code and message.
			if ( $response_body ) {
				$response_data = json_decode( $response_body, true );
		
				// Extract the error code and message.
				if ( isset( $response_data['code'] ) && isset( $response_data['message'] ) ) {
					$error_message = __( 'Code', 'gravity-zwr' ) . ' - ' . $response_data['code'] . ', ' . __( 'Message', 'gravity-zwr' ) . ' - ' . $response_data['message'];
				} else {
					$error_message = print_r( $remote_request->get_response(), true ); // phpcs:ignore
				}
			} else {
				$error_message = __( 'No response body. Please make sure your Zoom App has been set up correctly.', 'gravity-zwr' );
			}
		
			// Log that registration failed.
			$this->add_feed_error(
				esc_html__( 'Zoom API error when attempting registration: ', 'gravity-zwr' ).$error_message,
				$feed,
				$entry,
				$form
			); // phpcs:ignore
			return false;

		} else {

			// Decode the response body to extract join_url
			$response_data = json_decode( $remote_request->get_body(), true );
			$join_url = isset( $response_data['join_url'] ) ? $response_data['join_url'] : '';
		
			/* translators: %s - either "webinar" or "meeting" depending on the type; %s - Meeting ID; %s - Join URL */
			$note = sprintf(
				__( 'Zoom registration was successful. %1$s ID: %2$s.', 'gravity-zwr' ),
				ucwords( rtrim( $meetingtype, 's' ) ),
				$feed['meta']['zoomWebinarID']
			);

			// Update the entry with the join url
			if ( $join_url_field_id ) {
				GFAPI::update_entry_field( $entry[ 'id' ], $join_url_field_id, $join_url );
			}
          
            // Log that the registrant was added.
            RGFormsModel::add_note( $entry[ 'id' ], 0, __( 'Zoom Webinar', 'gravity-zwr' ), esc_html( $note ), 'gravity-zwr', 'success' );
			$this->log_debug( __METHOD__ . '(): Registrant successfull: ' . print_r( $remote_request->get_body(), true ) ); // phpcs:ignore
		}

		return $entry;

	}


	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * The feedback callback for the settings fields.
	 *
	 * @param string $value The setting value for valid characters used by Zoom OAuth credentials.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ): bool {

		// If the value is empty, return true.
		if ( empty( $value ) ) {
			return true;
		}

		// define the allowed characters in a regular expression
		$allowed_regex = '/^[a-zA-Z0-9_-]+$/';

		// use preg_match function to check if the input matches the allowed pattern
		return preg_match( $allowed_regex, $value );
	}

	/**
	 * Retrieve the Zoom settings keys.
	 *
	 * @return array
	 */
	public function get_zoom_settings_keys() {

		$plugin_settings = GFCache::get( 'zwr_plugin_settings' );
		if ( ! $plugin_settings ) {
			$plugin_settings = $this->get_plugin_settings();
		}
		return $plugin_settings;
	}

	/**
	 * Get the default field keys
	 *
	 * @return array
	 */
	public function get_merge_field_default_keys() {
		return array(
			'first_name',
			'last_name',
			'email',
			'address',
			'city',
			'country',
			'zip',
			'state',
			'phone',
			'industry',
			'org',
			'job_title',
			'purchasing_time_frame',
			'role_in_purchase_process',
			'no_of_employees',
			'comments',
		);
	} // End get_merge_field_default_keys()

	/**
	 * Get Zoom Webinar registration merge fields for list.
	 *
	 * @return array
	 */
	public function get_list_merge_fields() {

		$fields = array(
			'first_name' 			   => array(
					'type' 		  => 'name',
					'name'		  => 'First Name',
					'description' => 'Registrant\'s First Name.',
					'required'	  => true,
				),
			'last_name' 			   => array(
					'type' 		  => 'name',
					'name'		  => 'Last Name',
					'description' => 'Registrant\'s Last Name.',
					'required'	  => false,
				),
			'email' 				   => array(
					'type' 		  => 'email',
					'name'		  => 'Email',
					'description' => 'Registrant\'s Email.',
					'required'	  => true,
				),
			'address' 				   => array(
					'type' 		  => 'address',
					'name'		  => 'Address',
					'description' => 'Registrant\'s address.',
					'required'	  => false,
				),
			'city' 					   => array(
					'type' 		  => 'address',
					'name'		  => 'City',
					'description' => 'Registrant\'s city.',
					'required'	  => false,
				),
			'country' 				   => array(
					'type' 		  => 'address',
					'name'		  => 'Country',
					'description' => 'Registrant\'s country.',
					'required'	  => false,
				),
			'zip'					   => array(
					'type' 		  => 'address',
					'name'		  => 'ZIP',
					'description' => 'Registrant\'s Zip/Postal Code.',
					'required'	  => false,
				),
			'state' 				   => array(
					'type' 		  => 'address',
					'name'		  => 'State',
					'description' => 'Registrant\'s State/Province.',
					'required'	  => false,
				),
			'phone' 				   => array(
					'type' 		  => 'phone',
					'name'		  => 'Phone',
					'description' => 'Registrant\'s Phone number.',
					'required'	  => false,
				),
			'industry' 				   => array(
					'type' 		  => 'string',
					'name'		  => 'Industry',
					'description' => 'Registrant\'s Industry.',
					'required'	  => false,
				),
			'org' 					   => array(
					'type' 		  => 'string',
					'name'		  => 'Organization',
					'description' => 'Registrant\'s Organization.',
					'required'	  => false,
				),
			'job_title' 			   => array(
					'type' 		  => 'string',
					'name'		  => 'Job Title',
					'description' => 'Registrant\'s job title.',
					'required'	  => false,
				),
			'purchasing_time_frame'    => array(
					'type' 		  => 'select',
					'name'		  => 'Purchase Time Frame',
					'description' => 'This field can be included to gauge interest of webinar attendees towards buying your product or service.<br>Purchasing Time Frame:<br>`Within a month`<br>`1-3 months`<br>`4-6 months`<br>`More than 6 months`<br>`No timeframe`',
					'required'	  => false,
				),
			'role_in_purchase_process' => array(
					'type' 	  	  => 'select',
					'name'		  => 'Role in Purchase',
					'description' => 'Role in Purchase Process:<br>`Decision Maker`<br>`Evaluator/Recommender`<br>`Influencer`<br>`Not involved` ',
					'required'	  => false,
				),
			'no_of_employees'		   => array(
					'type' 		  => 'select',
					'name'		  => 'Number of Employees',
					'description' => 'Number of Employees:<br>`1-20`<br>`21-50`<br>`51-100`<br>`101-500`<br>`500-1,000`<br>`1,001-5,000`<br>`5,001-10,000`<br>`More than 10,000`',
					'required'	  => false,
				),
			'comments' 				   => array(
					'type' 		  => 'text',
					'name'		  => 'Comments',
					'description' => 'A field that allows registrants to provide any questions or comments that they might have.',
					'required'	  => false,
				),
			'join_url' 				   => array(
					'type' 		  => 'hidden',
					'name'		  => 'Join Link (Auto-Populates)',
					'description' => 'If you would like to update a hidden field with the Join Link, you can choose the field here.',
					'required'	  => false,
				),
		);

		// Allow custom fields
		$fields = apply_filters( 'gravityzwr_registration_fields', $fields );
		return $fields;
	}


	/**
	 * Add HTML
	 *
	 * @param array $field
	 * @param boolean $echo
	 * @return void
	 */
	public function settings_html( $field, $echo = true ) {
        $html = $field[ 'args' ][ 'html' ];
		echo '</pre>'.wp_kses_post( $html ).'<pre>';
    } // End settings_html()

	
	/**
	 * Convert country code from address countries
	 *
	 * @param string $country
	 * @return string
	 */
	public function convert_country_to_iso3166( $country ) {
		$iso3166 = array( 'AF' => "AFGHANISTAN", 'AX' => "ÅLAND ISLANDS", 'AL' => "ALBANIA", 'DZ' => "ALGERIA", 'AS' => "AMERICAN SAMOA", 'AD' => "ANDORRA", 'AO' => "ANGOLA", 'AI' => "ANGUILLA", 'AQ' => "ANTARCTICA", 'AG' => "ANTIGUA AND BARBUDA", 'AR' => "ARGENTINA", 'AM' => "ARMENIA", 'AW' => "ARUBA", 'AU' => "AUSTRALIA", 'AT' => "AUSTRIA", 'AZ' => "AZERBAIJAN", 'BS' => "BAHAMAS", 'BH' => "BAHRAIN", 'BD' => "BANGLADESH", 'BB' => "BARBADOS", 'BY' => "BELARUS", 'BE' => "BELGIUM", 'BZ' => "BELIZE", 'BJ' => "BENIN", 'BM' => "BERMUDA", 'BT' => "BHUTAN", 'BO' => "BOLIVIA, PLURINATIONAL STATE OF", 'BA' => "BOSNIA AND HERZEGOVINA", 'BW' => "BOTSWANA", 'BV' => "BOUVET ISLAND", 'BR' => "BRAZIL", 'IO' => "BRITISH INDIAN OCEAN TERRITORY", 'BN' => "BRUNEI DARUSSALAM", 'BG' => "BULGARIA", 'BF' => "BURKINA FASO", 'BI' => "BURUNDI", 'KH' => "CAMBODIA", 'CM' => "CAMEROON", 'CA' => "CANADA", 'CV' => "CAPE VERDE", 'KY' => "CAYMAN ISLANDS", 'CF' => "CENTRAL AFRICAN REPUBLIC", 'TD' => "CHAD", 'CL' => "CHILE", 'CN' => "CHINA", 'CX' => "CHRISTMAS ISLAND", 'CC' => "COCOS (KEELING) ISLANDS", 'CO' => "COLOMBIA", 'KM' => "COMOROS", 'CG' => "CONGO", 'CD' => "CONGO, THE DEMOCRATIC REPUBLIC OF THE", 'CK' => "COOK ISLANDS", 'CR' => "COSTA RICA", 'CI' => "CÔTE D'IVOIRE", 'HR' => "CROATIA", 'CU' => "CUBA", 'CY' => "CYPRUS", 'CZ' => "CZECH REPUBLIC", 'DK' => "DENMARK", 'DJ' => "DJIBOUTI", 'DM' => "DOMINICA", 'DO' => "DOMINICAN REPUBLIC", 'EC' => "ECUADOR", 'EG' => "EGYPT", 'SV' => "EL SALVADOR", 'GQ' => "EQUATORIAL GUINEA", 'ER' => "ERITREA", 'EE' => "ESTONIA", 'ET' => "ETHIOPIA", 'FK' => "FALKLAND ISLANDS (MALVINAS)", 'FO' => "FAROE ISLANDS", 'FJ' => "FIJI", 'FI' => "FINLAND", 'FR' => "FRANCE", 'GF' => "FRENCH GUIANA", 'PF' => "FRENCH POLYNESIA", 'TF' => "FRENCH SOUTHERN TERRITORIES", 'GA' => "GABON", 'GM' => "GAMBIA", 'GE' => "GEORGIA", 'DE' => "GERMANY", 'GH' => "GHANA", 'GI' => "GIBRALTAR", 'GR' => "GREECE", 'GL' => "GREENLAND", 'GD' => "GRENADA", 'GP' => "GUADELOUPE", 'GU' => "GUAM", 'GT' => "GUATEMALA", 'GG' => "GUERNSEY", 'GN' => "GUINEA", 'GW' => "GUINEA-BISSAU", 'GY' => "GUYANA", 'HT' => "HAITI", 'HM' => "HEARD ISLAND AND MCDONALD ISLANDS", 'VA' => "HOLY SEE (VATICAN CITY STATE)", 'HN' => "HONDURAS", 'HK' => "HONG KONG", 'HU' => "HUNGARY", 'IS' => "ICELAND", 'IN' => "INDIA", 'ID' => "INDONESIA", 'IR' => "IRAN, ISLAMIC REPUBLIC OF", 'IQ' => "IRAQ", 'IE' => "IRELAND", 'IM' => "ISLE OF MAN", 'IL' => "ISRAEL", 'IT' => "ITALY", 'JM' => "JAMAICA", 'JP' => "JAPAN", 'JE' => "JERSEY", 'JO' => "JORDAN", 'KZ' => "KAZAKHSTAN", 'KE' => "KENYA", 'KI' => "KIRIBATI", 'KP' => "KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", 'KR' => "KOREA, REPUBLIC OF", 'KW' => "KUWAIT", 'KG' => "KYRGYZSTAN", 'LA' => "LAO PEOPLE'S DEMOCRATIC REPUBLIC", 'LV' => "LATVIA", 'LB' => "LEBANON", 'LS' => "LESOTHO", 'LR' => "LIBERIA", 'LY' => "LIBYAN ARAB JAMAHIRIYA", 'LI' => "LIECHTENSTEIN", 'LT' => "LITHUANIA", 'LU' => "LUXEMBOURG", 'MO' => "MACAO", 'MK' => "MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", 'MG' => "MADAGASCAR", 'MW' => "MALAWI", 'MY' => "MALAYSIA", 'MV' => "MALDIVES", 'ML' => "MALI", 'MT' => "MALTA", 'MH' => "MARSHALL ISLANDS", 'MQ' => "MARTINIQUE", 'MR' => "MAURITANIA", 'MU' => "MAURITIUS", 'YT' => "MAYOTTE", 'MX' => "MEXICO", 'FM' => "MICRONESIA, FEDERATED STATES OF", 'MD' => "MOLDOVA, REPUBLIC OF", 'MC' => "MONACO", 'MN' => "MONGOLIA", 'ME' => "MONTENEGRO", 'MS' => "MONTSERRAT", 'MA' => "MOROCCO", 'MZ' => "MOZAMBIQUE", 'MM' => "MYANMAR", 'NA' => "NAMIBIA", 'NR' => "NAURU", 'NP' => "NEPAL", 'NL' => "NETHERLANDS", 'AN' => "NETHERLANDS ANTILLES", 'NC' => "NEW CALEDONIA", 'NZ' => "NEW ZEALAND", 'NI' => "NICARAGUA", 'NE' => "NIGER", 'NG' => "NIGERIA", 'NU' => "NIUE", 'NF' => "NORFOLK ISLAND", 'MP' => "NORTHERN MARIANA ISLANDS", 'NO' => "NORWAY", 'OM' => "OMAN", 'PK' => "PAKISTAN", 'PW' => "PALAU", 'PS' => "PALESTINIAN TERRITORY, OCCUPIED", 'PA' => "PANAMA", 'PG' => "PAPUA NEW GUINEA", 'PY' => "PARAGUAY", 'PE' => "PERU", 'PH' => "PHILIPPINES", 'PN' => "PITCAIRN", 'PL' => "POLAND", 'PT' => "PORTUGAL", 'PR' => "PUERTO RICO", 'QA' => "QATAR", 'RE' => "REUNION", 'RO' => "ROMANIA", 'RU' => "RUSSIAN FEDERATION", 'RW' => "RWANDA", 'BL' => "SAINT BARTHÉLEMY", 'SH' => "SAINT HELENA", 'KN' => "SAINT KITTS AND NEVIS", 'LC' => "SAINT LUCIA", 'MF' => "SAINT MARTIN", 'PM' => "SAINT PIERRE AND MIQUELON", 'VC' => "SAINT VINCENT AND THE GRENADINES", 'WS' => "SAMOA", 'SM' => "SAN MARINO", 'ST' => "SAO TOME AND PRINCIPE", 'SA' => "SAUDI ARABIA", 'SN' => "SENEGAL", 'RS' => "SERBIA", 'SC' => "SEYCHELLES", 'SL' => "SIERRA LEONE", 'SG' => "SINGAPORE", 'SK' => "SLOVAKIA", 'SI' => "SLOVENIA", 'SB' => "SOLOMON ISLANDS", 'SO' => "SOMALIA", 'ZA' => "SOUTH AFRICA", 'GS' => "SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS", 'ES' => "SPAIN", 'LK' => "SRI LANKA", 'SD' => "SUDAN", 'SR' => "SURINAME", 'SJ' => "SVALBARD AND JAN MAYEN", 'SZ' => "SWAZILAND", 'SE' => "SWEDEN", 'CH' => "SWITZERLAND", 'SY' => "SYRIAN ARAB REPUBLIC", 'TW' => "TAIWAN, PROVINCE OF CHINA", 'TJ' => "TAJIKISTAN", 'TZ' => "TANZANIA, UNITED REPUBLIC OF", 'TH' => "THAILAND", 'TL' => "TIMOR-LESTE", 'TG' => "TOGO", 'TK' => "TOKELAU", 'TO' => "TONGA", 'TT' => "TRINIDAD AND TOBAGO", 'TN' => "TUNISIA", 'TR' => "TURKEY", 'TM' => "TURKMENISTAN", 'TC' => "TURKS AND CAICOS ISLANDS", 'TV' => "TUVALU", 'UG' => "UGANDA", 'UA' => "UKRAINE", 'AE' => "UNITED ARAB EMIRATES", 'GB' => "UNITED KINGDOM", 'US' => "UNITED STATES", 'UM' => "UNITED STATES MINOR OUTLYING ISLANDS", 'UY' => "URUGUAY", 'UZ' => "UZBEKISTAN", 'VU' => "VANUATU", 'VE' => "VENEZUELA", 'VN' => "VIET NAM", 'VG' => "VIRGIN ISLANDS, BRITISH", 'VI' => "VIRGIN ISLANDS, U.S.", 'WF' => "WALLIS AND FUTUNA", 'EH' => "WESTERN SAHARA", 'YE' => "YEMEN", 'ZM' => "ZAMBIA", 'ZW' => "ZIMBABWE" );
    
		// Normalize the input
		$country = strtoupper( trim( $country ) );
		
		// Check if the input is an ISO code
		if ( array_key_exists( $country, $iso3166 ) ) {
			return $country;
		}

		// Check if the input is a country name
		$country_name = strtoupper( $country );
		$country_code = array_search( $country_name, $iso3166 );

		return $country_code !== false ? $country_code : false;
	} // End convert_country_to_iso3166()

}