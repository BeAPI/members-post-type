<?php
/**
 * Member Post Type Role class.
 */
class MPT_Role {
    /**
     * Role name.
     *
     *
     * @access public
     * @var string
     */
    var $name;

    /**
     * List of capabilities the role contains.
     *
     *
     * @access public
     * @var array
     */
    var $capabilities;

    /**
     * Constructor - Set up object properties.
     *
     * The list of capabilities, must have the key as the name of the capability
     * and the value a boolean of whether it is granted to the role.
     *
     *
     * @access public
     *
     * @param string $role Role name.
     * @param array $capabilities List of capabilities.
     */
    function __construct( $role, $capabilities ) {
        $this->name = $role;
        $this->capabilities = $capabilities;
    }

    /**
     * Assign role a capability.
     *
     * @see MPT_Roles::add_cap() Method uses implementation for role.
     *
     * @access public
     *
     * @param string $cap Capability name.
     * @param bool $grant Whether role has capability privilege.
     */
    function add_cap( $cap, $grant = true ) {
        global $mpt_roles;

        if ( ! isset( $mpt_roles ) )
            $mpt_roles = new MPT_Roles();

        $this->capabilities[$cap] = $grant;
        $mpt_roles->add_cap( $this->name, $cap, $grant );
    }

    /**
     * Remove capability from role.
     *
     * This is a container for {@link MPT_Roles::remove_cap()} to remove the
     * capability from the role. That is to say, that {@link
     * MPT_Roles::remove_cap()} implements the functionality, but it also makes
     * sense to use this class, because you don't need to enter the role name.
     *
     *
     * @access public
     *
     * @param string $cap Capability name.
     */
    function remove_cap( $cap ) {
        global $mpt_roles;

        if ( ! isset( $mpt_roles ) )
            $mpt_roles = new MPT_Roles();

        unset( $this->capabilities[$cap] );
        $mpt_roles->remove_cap( $this->name, $cap );
    }

    /**
     * Whether role has capability.
     *
     * The capabilities is passed through the 'role_has_cap' filter. The first
     * parameter for the hook is the list of capabilities the class has
     * assigned. The second parameter is the capability name to look for. The
     * third and final parameter for the hook is the role name.
     *
     *
     * @access public
     *
     * @param string $cap Capability name.
     * @return bool True, if user has capability. False, if doesn't have capability.
     */
    function has_cap( $cap ) {
        $capabilities = apply_filters( 'mpt_role_has_cap', $this->capabilities, $cap, $this->name );
        if ( !empty( $capabilities[$cap] ) )
            return $capabilities[$cap];
        else
            return false;
    }
}