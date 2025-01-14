<?php

class MPT_Gravity_Forms {
	public function __construct() {
		add_filter( 'gform_custom_merge_tags', [ $this, 'custom_merge_tags' ] );
		add_filter( 'gform_replace_merge_tags', [ $this, 'replace_merge_tags' ] );

	}

	/**
	 * Create custom tags
	 *
	 * @param $merge_tag
	 *
	 * @return array
	 */
	public function custom_merge_tags( $merge_tag ) {
		$mpt_tags = [
			[
				'label' => 'Member Last Name',
				'tag'   => '{mpt:lastname}',
			],
			[
				'label' => 'Member First Name',
				'tag'   => '{mpt:firstname}',
			],
			[
				'label' => 'Member Email',
				'tag'   => '{mpt:email}',
			],
		];

		return array_merge( $merge_tag, $mpt_tags );
	}

	/**
	 * Replace custom tags
	 *
	 * @param $text
	 *
	 * @return array|mixed|string|string[]
	 */
	public function replace_merge_tags( $text ) {
		$mpt_lastname  = '{mpt:lastname}';
		$mpt_firstname = '{mpt:firstname}';
		$mpt_email     = '{mpt:email}';

		if ( ! str_contains( $text, $mpt_lastname ) && ! str_contains( $text, $mpt_firstname ) && ! str_contains( $text, $mpt_email ) ) {
			return $text;
		}

		$current_member = mpt_get_current_member();

		if ( empty( $current_member ) ) {
			return $text;
		}

		return str_replace( [ $mpt_lastname, $mpt_firstname, $mpt_email ], [ $current_member->last_name, $current_member->first_name, $current_member->email ], $text );
	}
}
