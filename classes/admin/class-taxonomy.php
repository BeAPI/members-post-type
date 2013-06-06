<?php
class MPT_Admin_Taxonomy {
    /**
     * __construct
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {
        // JS
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10, 1 );

        // Form fields
        add_action(MPT_TAXO_NAME . '_add_form_fields', array(__CLASS__, 'add_form'));
		add_action(MPT_TAXO_NAME . '_edit_form_fields', array(__CLASS__, 'edit_form'), 10, 2);

        // Save term
        add_action('created_' . MPT_TAXO_NAME, array(__CLASS__, 'created_term'), 10, 2);
        add_action('edited_' . MPT_TAXO_NAME, array(__CLASS__, 'created_term'), 10, 2);
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
        if ( $hook == 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == MPT_TAXO_NAME ) {
            wp_enqueue_style( MPT_CPT_NAME . '-terms', MPT_URL . '/assets/css/admin-term.css', array( ), MPT_VERSION, 'all' );

            wp_enqueue_script ( MPT_TAXO_NAME . '-terms', MPT_URL . '/assets/js/admin-term.js', array('jquery'), MPT_VERSION, true );
            wp_localize_script( MPT_TAXO_NAME . '-terms', 'mpt_term_l10n', array('remove_link_label' => __('Remove capability', 'mpt') ) );
        }
    }

    public static function add_form($taxonomy) {
        $term = new StdClass;
        $term->capabilities = array();

        // Call Template
        include( MPT_DIR . 'views/admin/term-fields.php');
    }

    public static function edit_form($term, $taxonomy) {
        // Get role
        $role = MPT_Roles::get_role( $term->slug );
		
        $term->capabilities = array_keys($role->capabilities);

        // Call Template
        include( MPT_DIR . 'views/admin/term-fields.php');
    }

    public static function created_term($term_id, $tt_id) {
        if ( isset($_POST['mpt-capabilities']) ) {
            // Take caps form _POST
            $caps = ( !isset($_POST['caps']) ) ? array() : (array) $_POST['caps'];
			
            // Filter array for remove empty values
            $caps = array_filter( $caps, 'strlen' );

            // Get term data
            $term = get_term( $term_id, MPT_TAXO_NAME );
			
            // IF empty, drop meta
            if ( empty($caps) ) {
                MPT_Roles::remove_all_caps( $term->slug );
            } else {
                MPT_Roles::remove_all_caps( $term->slug );
                foreach( $caps as $cap ) {
                    MPT_Roles::add_cap( $term->slug, $cap, true );
                }
            }
        }
    }
}