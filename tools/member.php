<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

$member = new MPT_Member( '162' );
var_dump($member);

