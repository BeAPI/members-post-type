<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

$roles = new MPT_Roles();
var_dump($roles);

$roles->add_role('role-2', 'Role 2', array('cap1' => true ));
var_dump($roles->get_role('role-2'));

$roles->add_cap('role-2', 'cap2', true );
var_dump($roles->get_role('role-2'));

$roles->remove_cap('role-2', 'cap2');
var_dump($roles->get_role('role-2'));

$roles->remove_role('role-2');
var_dump($roles);