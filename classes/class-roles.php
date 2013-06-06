<?php
/**
 * Members Post Type Roles.
 *
 * The role feature is simple, the structure is organized in term on a specific taxonomy "members-role"
 * The capabilities are stored as an array in a term meta values with 'capabilities' key.
 */
class MPT_Roles {
	/**
	 * List of roles (WP term objects).
	 *
	 * @access public
	 * @var array
	 */
	public static $roles;

	/**
	 * List of the role objects. (MPT_Role objects)
	 *
	 * @access public
	 * @var array
	 */
	public static $role_objects = array();

	/**
	 * List of role names.
	 *
	 * @access public
	 * @var array
	 */
	public static $role_names = array();

	/**
	 * Constructor
	 *
	 *
	 */
	public function __construct() {}

	/**
	 * Set up the object properties.
	 *
	 * @access private
	 */
	public static function init () {
		self::$roles = self::_get_roles();
		if ( empty( self::$roles ) )
			return false;

		self::$role_objects = array();
		self::$role_names =  array();
		foreach ( self::$roles as $role ) {
			self::$role_objects[$role->slug] = new MPT_Role( $role->slug, (array) get_term_taxonomy_meta( $role->term_taxonomy_id, 'capabilities', true) );
			self::$role_names[$role->slug] = $role->name;
		}

		return true;
	}

	/**
	 * Add role name with capabilities to list.
	 *
	 * Updates the list of roles, if the role doesn't already exist.
	 *
	 * The capabilities are defined in the following format `array( 'read' => true );`
	 * To explicitly deny a role a capability you set the value for that capability to false.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 * @param string $display_name Role display name.
	 * @param array $capabilities List of role capabilities in the above format.
	 * @return null|MPT_Role MPT_Role object if role is added, null if already exists.
	 */
	public static function add_role( $role, $display_name, $capabilities = array() ) {
		if ( isset( self::$roles[$role] ) )
			return false;

		// Try term insertion
		$term_result = wp_insert_term( $display_name, MPT_TAXO_NAME, array('slug' => $role) );
		if ( $term_result == false || is_wp_error($term_result) ) {
			return false;
		}

		// Get new term data
		$term = get_term($term_result['term_id'], MPT_TAXO_NAME);

		// Update term meta with capabilities
		update_term_taxonomy_meta( $term_result['term_taxonomy_id'], 'capabilities', $capabilities );

		// Refresh values
		self::$roles[$term->slug] = $term;
		self::$role_objects[$term->slug] = new MPT_Role( $term->slug, $capabilities );
		self::$role_names[$term->slug] = $display_name;
		
		return self::$role_objects[$term->slug];
	}

	/**
	 * Remove role by name.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 */
	public static function remove_role( $role ) {
		if ( ! isset( self::$role_objects[$role] ) )
			return false;

		unset( self::$role_objects[$role] );
		unset( self::$role_names[$role] );
		unset( self::$roles[$role] );

		$term = get_term_by( 'slug', $role, MPT_TAXO_NAME );
		if ( $term != false ) {
			wp_delete_term( $term->term_id, MPT_TAXO_NAME );
		}

		return true;
	}

	/**
	 * Add capability to role.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 * @param string $cap Capability name.
	 * @param bool $grant Optional, default is true. Whether role is capable of performing capability.
	 */
	public static function add_cap( $role, $cap, $grant = true ) {
		if ( ! isset( self::$roles[$role] ) )
			return false;

		// Get current capabilities
		$capabilities = get_term_taxonomy_meta( self::$roles[$role]->term_taxonomy_id, 'capabilities', true);
		if ( $capabilities == false ) {
			$capabilities = array();
		}

		// Add the new cap
		$capabilities[$cap] = $grant;

		// Save new capabilities
		update_term_taxonomy_meta( self::$roles[$role]->term_taxonomy_id, 'capabilities', $capabilities );

		// Refesh variable
		self::$role_objects[$role] = new MPT_Role( $role, $capabilities );
		
		return true;
	}

	/**
	 * Remove capability from role.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 * @param string $cap Capability name.
	 */
	public static function remove_cap( $role, $cap ) {
		if ( ! isset( self::$roles[$role] ) )
			return false;

		// Get current capabilities
		$capabilities = get_term_taxonomy_meta( self::$roles[$role]->term_taxonomy_id, 'capabilities', true);
		if ( $capabilities == false ) {
			$capabilities = array();
		}

		// Remove the cap
		unset($capabilities[$cap]);

		// Save new capabilities
		update_term_taxonomy_meta( self::$roles[$role]->term_taxonomy_id, 'capabilities', $capabilities );

		// Refesh variable
		self::$role_objects[$role] = new MPT_Role( $role, $capabilities );

		return true;
	}

	/**
	 * Remove all capabilities for role.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 * @param string $cap Capability name.
	 */
	public static function remove_all_caps( $role ) {
		if ( ! isset( self::$roles[$role] ) )
			return false;

		// Save new
		update_term_taxonomy_meta( self::$roles[$role]->term_taxonomy_id, 'capabilities', array() );

		// Refesh variable
		self::$role_objects[$role] = new MPT_Role( $role, array() );

		return true;
	}
	
	/**
	 * Retrieve all roles. (WP_Term object)
	 *
	 * @access public
	 *
	 * @return array array with WP_Term objects
	 */
	public static function get_roles() {
		return self::$roles;
	}
	
	/**
	 * Retrieve all roles (MPT_Role object)
	 *
	 * @access public
	 *
	 * @return array Array with MPT_Role objects
	 */
	public static function get_roles_objects() {
		return self::$role_objects;
	}
	
	/**
	 * Retrieve all roles names.
	 *
	 * @access public
	 *
	 * @return array Array with role names
	 */
	public static function get_roles_names() {
		return self::$role_names;
	}

	/**
	 * Retrieve role object by name.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 * @return object|null Null, if role does not exist. MPT_Role object, if found.
	 */
	public static function get_role( $role ) {
		if ( isset( self::$role_objects[$role] ) )
			return self::$role_objects[$role];
		else
			return null;
	}

	/**
	 * Retrieve list of role names.
	 *
	 * @access public
	 *
	 * @return array List of role names.
	 */
	public static function get_names() {
		return self::$role_names;
	}

	/**
	 * Whether role name is currently in the list of available roles.
	 *
	 * @access public
	 *
	 * @param string $role Role name to look up.
	 * @return bool
	 */
	public static function is_role( $role ) {
		return isset( self::$role_names[$role] );
	}

    /**
     * Wrap get terms into a method, that allow to build an array with term slug as index.
     * 
     * @param array $args Description.
     *
     * @access private
     *
     * @return array Value.
     */
	private static function _get_roles( $args = array() ) {
		global $wpdb;
		
		// Parse vs defaults
		$args = wp_parse_args( $args, array('hide_empty' => 0) );

		$_terms = array();
		$terms = get_terms( MPT_TAXO_NAME, $args );
		if ( $terms != false && is_array($terms) && !is_wp_error($terms) ) {
			foreach( (array) $terms as $term ) {
				$_terms[$term->slug] = $term;
			}
		}
		
		return $_terms;
	}
}