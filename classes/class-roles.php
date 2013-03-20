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
	public $roles;

	/**
	 * List of the role objects. (MPT_Role objects)
	 *
	 * @access public
	 * @var array
	 */
	public $role_objects = array();

	/**
	 * List of role names.
	 *
	 * @access public
	 * @var array
	 */
	public $role_names = array();

	/**
	 * Constructor
	 *
	 *
	 */
	public function __construct() {
		return $this->_init();
	}

	/**
	 * Set up the object properties.
	 *
	 * @access private
	 */
	private function _init () {
		$this->roles = $this->_get_roles();
		if ( empty( $this->roles ) )
			return false;

		$this->role_objects = array();
		$this->role_names =  array();
		foreach ( $this->roles as $role ) {
			$this->role_objects[$role->slug] = new MPT_Role( $role->slug, (array) get_term_taxonomy_meta( $role->term_taxonomy_id, 'capabilities', true) );
			$this->role_names[$role->slug] = $role->name;
		}

		return true;
	}

	/**
	 * Reinitialize the object
	 *
	 * Recreates the role objects. This is typically called only by switch_to_blog()
	 * after switching wpdb to a new blog ID.
	 *
	 * @access public
	 */
	public function reinit() {
		return $this->_init();
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
	public function add_role( $role, $display_name, $capabilities = array() ) {
		if ( isset( $this->roles[$role] ) )
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
		$this->roles[$term->slug] = $term;
		$this->role_objects[$term->slug] = new MPT_Role( $term->slug, $capabilities );
		$this->role_names[$term->slug] = $display_name;
		
		return $this->role_objects[$term->slug];
	}

	/**
	 * Remove role by name.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 */
	public function remove_role( $role ) {
		if ( ! isset( $this->role_objects[$role] ) )
			return false;

		unset( $this->role_objects[$role] );
		unset( $this->role_names[$role] );
		unset( $this->roles[$role] );

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
	public function add_cap( $role, $cap, $grant = true ) {
		if ( ! isset( $this->roles[$role] ) )
			return false;

		// Get current capabilities
		$capabilities = get_term_taxonomy_meta( $this->roles[$role]->term_taxonomy_id, 'capabilities', true);
		if ( $capabilities == false ) {
			$capabilities = array();
		}

		// Add the new cap
		$capabilities[$cap] = $grant;

		// Save new capabilities
		update_term_taxonomy_meta( $this->roles[$role]->term_taxonomy_id, 'capabilities', $capabilities );

		// Refesh variable
		$this->role_objects[$role] = new MPT_Role( $role, $capabilities );
		
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
	public function remove_cap( $role, $cap ) {
		if ( ! isset( $this->roles[$role] ) )
			return false;

		// Get current capabilities
		$capabilities = get_term_taxonomy_meta( $this->roles[$role]->term_taxonomy_id, 'capabilities', true);
		if ( $capabilities == false ) {
			$capabilities = array();
		}

		// Remove the cap
		unset($capabilities[$cap]);

		// Save new capabilities
		update_term_taxonomy_meta( $this->roles[$role]->term_taxonomy_id, 'capabilities', $capabilities );

		// Refesh variable
		$this->role_objects[$role] = new MPT_Role( $role, $capabilities );

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
	public function remove_all_caps( $role ) {
		if ( ! isset( $this->roles[$role] ) )
			return false;

		// Save new
		update_term_taxonomy_meta( $this->roles[$role]->term_taxonomy_id, 'capabilities', array() );

		// Refesh variable
		$this->role_objects[$role] = new MPT_Role( $role, array() );

		return true;
	}

	/**
	 * Retrieve role object by name.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 * @return object|null Null, if role does not exist. MPT_Role object, if found.
	 */
	public function get_role( $role ) {
		if ( isset( $this->role_objects[$role] ) )
			return $this->role_objects[$role];
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
	public function get_names() {
		return $this->role_names;
	}

	/**
	 * Whether role name is currently in the list of available roles.
	 *
	 * @access public
	 *
	 * @param string $role Role name to look up.
	 * @return bool
	 */
	public function is_role( $role ) {
		return isset( $this->role_names[$role] );
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
	private function _get_roles( $args = array() ) {
		// Parse vs defaults
		$args = wp_parse_args( $args, array('hide_empty' => 0, '') );

		$_terms = array();
		$terms = get_terms( MPT_TAXO_NAME, $args );
		foreach( (array) $terms as $term ) {
			$_terms[$term->slug] = $term;
		}

		return $_terms;
	}
}