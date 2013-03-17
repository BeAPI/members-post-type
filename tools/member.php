<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

mpt_add_role('role-2', 'Role 2', array('cap1' => true ));
$role = mpt_get_role('role-2');
$role->add_cap( 'cap3', true );

global $mpt_roles;
print_r($mpt_roles);

$member = new MPT_Member( '162' );
var_dump($member);

$member->set_role('role-2');
var_dump($member);

var_dump( member_can($member, 'cap3') );
var_dump( member_can($member, 'cap4') );

mpt_remove_role('role-2');
