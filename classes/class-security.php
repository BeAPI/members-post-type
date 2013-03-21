<?php
class MPT_Security {
	public function __construct() {
		
	}
	
	public static function get_password_strength( $member, $password1 ) {
		$symbolSize = 0;

		// password < 6
		if ( strlen( $password1 ) < 6 )
			return 1;

		//password1 == username
		if ( strtolower( $password1 ) == strtolower( $member->username ) )
			return 2;

		if ( preg_match( '/[0-9]/', $password1 ) )
			$symbolSize += 10;
		if ( preg_match( '/[a-z]/', $password1 ) )
			$symbolSize += 26;
		if ( preg_match( '/[A-Z]/', $password1 ) )
			$symbolSize += 26;
		if ( preg_match( '/[^a-zA-Z0-9]/', $password1 ) )
			$symbolSize += 31;

		$natLog = log( pow( $symbolSize, strlen( $password1 ) ) );
		$score = $natLog / log( 2 );

		if ( $score < 40 )
			return 2;

		if ( $score < 56 )
			return 3;
		
		return 4;
	}
}