<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

$result = MPT_Member_Utility::create_member( 'beapi', 'beapi!56', 'amaury1@beapi.fr' );
//wp_delete_post($result, true);

die('insertion OK');
