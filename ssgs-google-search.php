<?php
/*
Plugin Name: Server-Side Google Search
Description: Google Custom Search for your site, without JavaScript
Version: 1.0.0
Author: Aptivate, WebshopLogic
License: GPLv2 or later
Text Domain: ssgs
Requires at least: 3.7
Tested up to: 3.9
*/

if ( ! class_exists( 'SS_Google_Search' ) ) {

class SS_Google_Search {

	public $plugin_path;

	public $plugin_url;


	function __construct() {

		include_once( 'ssgs-admin-page.php' );

		add_action( 'init', array( $this, 'init' ), 0 );

		include_once( 'ssgs-widget.php' );
		do_action( 'ssgs_init' );
	}

	public function init() {

		load_plugin_textdomain( 'ssgs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


		global $ssgs_admin_page;
		$ssgs_admin_page = new SSGS_Admin_Page;

		$options = get_option('ssgs_general_settings');

		wp_enqueue_style( 'ssgs', plugins_url('ssgs.css', __FILE__) );

		$script_params = array(
			'google_search_engine_id' => $options['google_search_engine_id']
		);

		add_shortcode( 'ss_google_search', array( $this, 'ss_google_search_shortcode' ));
		add_shortcode( 'ss_google_searchbox', array( $this, 'ss_google_searchbox_shortcode' ));

		do_action( 'ssgs_init', $this );
	}

	public function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	function plugin_url() {
		if ( $this->plugin_url ) return $this->plugin_url;
		return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}


	function ss_google_search_shortcode( $atts ){

		$options = get_option( 'ssgs_general_settings' );

		//if ($options['use_default_correction_css'] == 1)
		//	wp_enqueue_style( 'ssgs', plugins_url('ssgs.css', __FILE__) );

		if ($options['searchbox_before_results'] == 1)
			$gcse_code = 'search';
		else
			$gcse_code = 'searchresults-only';

		$content  = '<div class="ssgs_wrapper" id="ssgs_wrapper_id">';
		//$content .= '<gcse:searchresults-only linktarget="_self"></gcse:searchresults-only>';

		//You can use HTML5-valid div tags as long as you observe these guidelines: //20140423
		//The class attribute must be set to gcse-XXX
		//Any attributes must be prefixed with data-.
		//$content .= '<gcse:' . $gcse_code . ' linktarget="_self"></gcse:' . $gcse_code . '>';
		$content .= '<div class="gcse-' . $gcse_code . '" data-linktarget="_self"></div>';

		$content .= '</div>';

		$content = apply_filters('ssgs_shortcode_content', $content);

		return $content;

	}

	function ss_google_searchbox_shortcode( $atts ){

		$options = get_option( 'ssgs_general_settings' );

		//if ($options['use_default_correction_css'] == 1)
		//	wp_enqueue_style( 'ssgs', plugins_url('ssgs.css', __FILE__) );

		$search_gcse_page_url = get_page_link( $options['search_gcse_page_id'] );

		$content  = '<div class="ssgs_wrapper" id="ssgs_widget_wrapper_id">';
		//You can use HTML5-valid div tags as long as you observe these guidelines: //20140423
		//The class attribute must be set to gcse-XXX
		//Any attributes must be prefixed with data-.
		//$content .= '<gcse:searchbox-only resultsUrl="' . $search_gcse_page_url . '"></gcse:searchbox-only>';
		$content .= '<div class="gcse-searchbox-only" data-resultsUrl="' . $search_gcse_page_url . '"></div>';

		$content .= '</div>';

		$content = apply_filters('ssgs_searchbox_shortcode_content', $content);

		return $content;

	}
}

//Init SS_Google_Search class
$GLOBALS['ss_google_search'] = new SS_Google_Search();

}

?>
