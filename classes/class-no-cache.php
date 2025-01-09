<?php
class MPT_No_Cache {
	public function __construct() {
		// Compat Rocket
		add_filter( 'do_rocket_generate_caching_files', [ $this, 'no_cache_for_page' ] );
		// Compat Varnish
		add_filter( 'cache_control_nocacheables', [ $this, 'non_cacheable_pages_templates' ] );
	}

	/**
	 * Disable cache file generation on specific pages template
	 *
	 * @author Egidio CORICA
	 */
	public function no_cache_for_page( $filter ) {
		return $this->is_page_to_exclude() ? false : $filter;
	}

	/**
	 * Exclude the specific pages from the cache.
	 *
	 * @param bool $status
	 *
	 * @return bool
	 * @author Egidio CORICA
	 */
	public function non_cacheable_pages_templates( bool $status ): bool {
		return $this->is_page_to_exclude() ? true : $status;
	}

	/**
	 * Get template to exclude from cache
	 *
	 * @return bool
	 * @author Egidio CORICA
	 */
	public function is_page_to_exclude(): bool {
		return MPT_Main::is_action_page();
	}
}
