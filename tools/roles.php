<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

MPT_Roles::add_role('role-2', 'Role 2', array('cap1' => true ));
var_dump(MPT_Roles::get_role('role-2'));

MPT_Roles::add_cap('role-2', 'cap2', true );
var_dump(MPT_Roles::get_role('role-2'));

MPT_Roles::remove_cap('role-2', 'cap2');
var_dump(MPT_Roles::get_role('role-2'));

MPT_Roles::remove_role('role-2');
var_dump($roles);