<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

$result = MPT_User_Utility::create_user( 'beapi', 'beapi!56', 'amaury1@beapi.fr' );
//wp_delete_post($result, true);

die('insertion OK');
