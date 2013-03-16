<?php
/**
 * Members Post Type Roles.
 *
 * The role option is simple, the structure is organized by role name that store
 * the name in value of the 'name' key. The capabilities are stored as an array
 * in the value of the 'capability' key.
 *
 * <code>
 * array (
 *		'rolename' => array (
 *			'name' => 'rolename',
 *			'capabilities' => array()
 *		)
 * )
 * </code>
 */
class MPT_Roles {
	/**
	 * List of roles and capabilities.
	 *
	 * @access public
	 * @var array
	 */
	public $roles;

	/**
	 * List of the role objects.
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
		$this->roles = get_option( 'mpt-roles' );
		if ( empty( $this->roles ) )
			return false;

		$this->role_objects = array();
		$this->role_names =  array();
		foreach ( array_keys( $this->roles ) as $role ) {
			$this->role_objects[$role] = new MPT_Role( $role, $this->roles[$role]['capabilities'] );
			$this->role_names[$role] = $this->roles[$role]['name'];
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

		$this->roles[$role] = array(
			'name' => $display_name,
			'capabilities' => $capabilities
			);

		update_option( 'mpt-roles', $this->roles );
		$this->role_objects[$role] = new MPT_Role( $role, $capabilities );
		$this->role_names[$role] = $display_name;
		return $this->role_objects[$role];
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

		update_option( 'mpt-roles', $this->roles );
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

		$this->roles[$role]['capabilities'][$cap] = $grant;
		update_option( 'mpt-roles', $this->roles );
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

		unset( $this->roles[$role]['capabilities'][$cap] );
		update_option( 'mpt-roles', $this->roles );
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
}