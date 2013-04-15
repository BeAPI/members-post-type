<?php
class MPT_Admin_Main {
	/**
     * Register hooks
     * 
     * @access public
     *
     * @return void.
     */
	public function __construct( ) {
		// Init settings main page
		new MPT_Admin_Settings_Main();
	}
}