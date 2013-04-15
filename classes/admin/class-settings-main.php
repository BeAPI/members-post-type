<?php
class MPT_Admin_Settings_Main {
	static $settings_api;
	static $id = 'mpt-main';
	
	/**
     * __construct
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct( ) {
		self::$settings_api = new WeDevs_Settings_API();

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10, 1 );
	}

	/**
     * admin_enqueue_scripts
     * 
     * @param mixed $hook Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function admin_enqueue_scripts( $hook ) {
		if ( $hook == 'settings_page_' . 'mpt-settings' ) {
			wp_enqueue_script( MPT_CPT_NAME . '-admin-settings', MPT_URL . 'assets/js/admin-settings.js', array('jquery'), MPT_VERSION, true );
		}
	}

	/**
     * admin_menu
     * 
     * @param mixed $hook Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function admin_menu( ) {
		add_options_page( __('Members Post Type', 'mpt'), __('Members Post Type', 'mpt'), 'manage_options', 'mpt-settings', array( __CLASS__, 'render_page_settings' ) );
	}

    /**
     * render_page_settings
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function render_page_settings() {
		include (MPT_DIR . 'views/admin/page-settings.php');
	}

    /**
     * admin_init
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function admin_init( ) {
        //set the settings
        self::$settings_api->set_sections( self::get_settings_sections() );
        self::$settings_api->set_fields( self::get_settings_fields() );

        //initialize settings
        self::$settings_api->admin_init();
	}

    public static function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'mpt-main',
				'tab_label' => __( 'General', 'mpt' ),
                'title' => __( 'General features', 'mpt' ),
                'desc' => false,
            ),
            array(
                'id' => 'mpt-pages',
				'tab_label' => __( 'Feature Pages', 'mpt' ),
                'title' => __( 'Feature Pages', 'mpt' ),
                'desc' => __( 'You must define here the pages containing the WordPress shortcodes for different features (login, registration, etc).', 'mpt' ),
            ),
            array(
                'id' => 'mpt-security',
				'tab_label' => __( 'Security', 'mpt' ),
                'title' => __( 'Password strength', 'mpt' ),
                'desc' => __('Enforce a specific password strength for your members.', 'mpt'),
            )
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public static function get_settings_fields() {
        $settings_fields = array(
            'mpt-main' => array(
                array(
                    'name' => 'allow-signon-email',
                    'label' => __( 'Sign-on method', 'mpt' ),
                    'options' => __( 'Allow email sign-on ?', 'mpt' ),
                    'type' => 'checkbox',
                    'default' => 1,
                    'desc' => __('Do not change this value if you have already members! At the risk of breaking your site!', 'mpt' )
                ),
                array(
                    'name' => 'unique-email',
                    'label' => __( 'Email constraint', 'mpt' ),
                    'options' => __( 'Email must be unique ?', 'mpt' ),
                    'desc' => __('Do not change this value if you have already members! At the risk of breaking your site! This option is automatically enabled when you allow email sign-on.', 'mpt'),
                    'type' => 'checkbox',
                    'default' => 1
                ),
            ),
            'mpt-pages' => array(
                array(
                    'name' => 'page-registration',
                    'label' => __( 'Registration', 'mpt' ),
                    //'desc' => __( 'Dropdown', 'mpt' ),
                    'type' => 'select',
                    'options' => self::_get_pages()
                ),
                array(
                    'name' => 'page-login',
                    'label' => __( 'Login/logout', 'mpt' ),
                    //'desc' => __( 'Dropdown', 'mpt' ),
                    'type' => 'select',
                    'options' => self::_get_pages()
                ),
                array(
                    'name' => 'page-change-password',
                    'label' => __( 'Change password', 'mpt' ),
                    //'desc' => __( 'Dropdown', 'mpt' ),
                    'type' => 'select',
                    'options' => self::_get_pages()
                ),
                array(
                    'name' => 'page-lost-password',
                    'label' => __( 'Lost password', 'mpt' ),
                    //'desc' => __( 'Dropdown', 'mpt' ),
                    'type' => 'select',
                    'options' => self::_get_pages()
                ),
            ),
            'mpt-security' => array(
				array(
                    'name' => 'mode',
                    'label' => __( 'Mode', 'mpt' ),
                    'desc' => __( 'You can apply one of three modes for password security, no constraint, automatic cloned from WordPress JS meter and full manual mode', 'mpt' ),
                    'type' => 'radio',
                    'default' => 'none',
					'options' => array(
						'none'		=> __('Disabled', 'mpt'),
						'auto'		=> __('Auto', 'mpt'),
						'custom'	=> __('Custom', 'mpt'),
					)
                ),
                array(
                    'name' => 'force-refresh-login',
                    'label' => __( 'Force refresh login', 'mpt' ),
                    'desc' => __( 'When changing your password policy, this option forces members to change their password once they are logged in! If it does not meet your criteria of course!', 'mpt' ),
                    'type' => 'checkbox',
                    'default' => 0,
					'sanitize_callback' => 'intval'
                ),
				array(
                    'name' => 'auto-mode',
                    'label' => __( 'Password strength - auto mode', 'mpt' ),
                    'desc' => __( 'WordPress offers a interactive password meter (with JS) when editing passwords. This indicator score passwords on 4 levels: 1 = very weak; 2 = weak; 3 = medium; 4 = strong', 'mpt' ),
                    'type' => 'metabox',
                ),
				array(
                    'name' => 'auto-mode-level',
                    'label' => __( 'Level required', 'mpt' ),
                    'type' => 'radio',
                    'default' => 'none',
					'options' => array(
						'1'	=> __('Very weak', 'mpt'),
						'2'	=> __('Weak', 'mpt'),
						'3'	=> __('Medium', 'mpt'),
						'4'	=> __('Strong', 'mpt'),
					)
                ),
				array(
                    'name' => 'custom-mode',
                    'label' => __( 'Password strength - Custom mode', 'mpt' ),
                    'desc' => __( 'This mode allows you to fine tune the security policy password', 'mpt' ),
                    'type' => 'metabox',
                ),
                array(
                    'name' => 'minimum-length',
                    'label' => __( 'Minimum length', 'mpt' ),
                    'desc' => __( 'Tip: password longer than 6 characters is highly recommended!', 'mpt' ),
                    'type' => 'text',
                    'default' => 6,
					'sanitize_callback' => 'intval'
                ),
                array(
                    'name' => 'user-data-password',
                    'label' => __( 'User data on password', 'mpt' ),
                    'desc' => __( 'Prohibit the presence of the username, email, first name, last name in the password', 'mpt' ),
                    'type' => 'checkbox',
                    'default' => 0
                ),
                array(
                    'name' => 'uppercase-character',
                    'label' => __( 'Uppercase character', 'mpt' ),
                    'desc' => __( 'Forces the presence of an uppercase character in the password', 'mpt' ),
                    'type' => 'checkbox',
                    'default' => 0
                ),
                array(
                    'name' => 'lowercase-character',
                    'label' => __( 'Lowercase character', 'mpt' ),
                    'desc' => __( 'Forces the presence of an lowercase character in the password', 'mpt' ),
                    'type' => 'checkbox',
                    'default' => 0
                ),
                array(
                    'name' => 'number-character',
                    'label' => __( 'Number character', 'mpt' ),
                    'desc' => __( 'Forces the presence of an number character in the password', 'mpt' ),
                    'type' => 'checkbox',
                    'default' => 0
                ),
                array(
                    'name' => 'special-character',
                    'label' => __( 'Special character', 'mpt' ),
                    'options' => __( 'Forces the presence of an special character in the password', 'mpt' ),
                    'type' => 'checkbox',
                    'default' => 0,
                    'desc' => __('Special characters are often assimilated to punctuation character. (?!&")', 'mpt')
                ),
                array(
                    'name' => 'blacklist-keywords',
                    'label' => __( 'Blacklist keywords', 'mpt' ),
                    'desc' => __( 'You must separate blacklist words with commas. These words can not be contained in the passwords of your members.', 'mpt' ),
                    'type' => 'text',
                    'default' => ''
                ),
				array(
                    'name' => 'password-history',
                    'label' => __( 'Password Policies', 'mpt' ),
                    'desc' => __( 'A password policy is a set of rules designed to enhance security by encouraging users to employ strong passwords and use them properly', 'mpt' ),
                    'type' => 'metabox',
                ),
                array(
                    'name' => 'aging',
                    'label' => __( 'Aging', 'mpt' ),
                    'desc' => __( 'How many days old can a password be before requiring it be changed? Not recommended. 0 disables this feature. Default: 0.', 'mpt' ),
                    'type' => 'text',
                    'default' => 0,
					'sanitize_callback' => 'intval'
                ),
				array(
                    'name' => 'grace_period',
                    'label' => __( 'Grace Period', 'mpt' ),
                    'desc' => __( 'How many minutes should a user have to change their password once they know it has expired? Must be >= 5. Default: 15.', 'mpt' ),
                    'type' => 'text',
                    'default' => 0,
					'sanitize_callback' => 'intval'
                ),
				array(
                    'name' => 'history',
                    'label' => __( 'History', 'mpt' ),
                    'desc' => __( 'How many passwords should be remembered? Prevents reuse of old passwords. 0 disables this feature. Default: 0.', 'mpt' ),
                    'type' => 'text',
                    'default' => 0,
					'sanitize_callback' => 'intval'
                ),
            )
        );

        return $settings_fields;
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    private static function _get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

    /**
     * TODO: Keep logic
     * 
     * @param mixed $input Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function validate_input( $input ) {
		// Cleanup input
		$input = stripslashes_deep($input);
		
		// Create our array for storing the validated options
		$output = array();
		
		// Loop through each of the incoming options
		foreach( self::get_default_options() as $key => $value ) {
			if( isset( $input[$key] ) ) {
				$output[$key] = strip_tags( $input[ $key ] );
			} else {
				$output[$key] = 0;
			}
		}
		
		// Constraint & Signon
		if ( (int) $output['allow-signon-email'] == 1 ) {
			$output['unique-email'] = 1;
		}
		
		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'mpt_settings_validate_input', $output, $input, self::$id );
	}
}
