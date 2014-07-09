<?php
/*
Plugin Name: WP Google Search
Plugin URI: http://webshoplogic.com/
Description: This plugin gives a very simple way to integrate Google Search into your WordPress site.  
Version: 1.0.1
Author: WebshopLogic
Author URI: http://webshoplogic.com/
License: GPLv2 or later
Text Domain: wgs
Requires at least: 3.7
Tested up to: 3.9
*/

if ( ! class_exists( 'WP_Google_Search' ) ) {

class WP_Google_Search {

	public $plugin_path;

	public $plugin_url;


	function __construct() {

		include_once( 'wgs-admin-page.php' );
		
		add_action( 'init', array( $this, 'init' ), 0 );
		
		register_activation_hook( __FILE__, array( $this, 'wgs_activation' ) );
		
		$options = get_option( 'wgs_general_settings' );

		//if (!empty($options['google_search_engine_id'])) { //$options['enable_plugin']

			include_once( 'wgs-widget.php' );
			do_action( 'wgs_init' );

		//}

	}

	public function init() {

		load_plugin_textdomain( 'wgs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


		global $wgs_admin_page;
		$wgs_admin_page = new WGS_Admin_Page;

		$options = get_option('wgs_general_settings');

		//if ( !empty($options['google_search_engine_id']) ) { //$options['enable_plugin']
			
			wp_register_script(
				'google_cse_v2',
				$this->plugin_url() . '/assets/js/google_cse_v2.js',
				array( // dependencies 
  						),
 					1.0,
					true
			);

			wp_enqueue_script( 'google_cse_v2' );
			
			if ($options['use_default_correction_css'] == 1)
				wp_enqueue_style( 'wgs', plugins_url('wgs.css', __FILE__) );
			
			$script_params = array(
				'google_search_engine_id' => $options['google_search_engine_id']
				);
				
			wp_localize_script( 'google_cse_v2', 'scriptParams', $script_params );			

			add_shortcode( 'wp_google_search', array( $this, 'wp_google_search_shortcode' ));
			add_shortcode( 'wp_google_searchbox', array( $this, 'wp_google_searchbox_shortcode' ));

			do_action( 'wgs_init', $this );

		//}

	}
	
	public function wgs_activation() {

		//create search page if not exists
			
		$options = get_option( 'wgs_general_settings' );
		
		$search_gcse_page_id = $options['search_gcse_page_id'];

		if ($options['search_gcse_page_id'] == null) {

			$search_gcse_page = array(
			  //'ID'             => [ <post id> ] // Are you updating an existing post?
			  'post_content'   => '[wp_google_search]', //'<gcse:searchresults-only linktarget="_self"></gcse:searchresults-only>', //[ <string> ] // The full text of the post.
			  'post_name'      => 'search_gcse', //[ <string> ] // The name (slug) for your post
			  'post_title'     => __('Search Results','wgs'), //[ <string> ] // The title of your post.
			  'post_status'    => 'publish', //[ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
			  'post_type'      => 'page', //[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
			  'post_author'    => get_current_user_id(), //[ <user ID> ] // The user ID number of the author. Default is the current user ID.
			  'post_excerpt'   => __('Search Results','wgs'), //[ <string> ] // For all your post excerpt needs.
			  'post_date'      => date('Y-m-d H:i:s'), //[ Y-m-d H:i:s ] // The time post was made.
			  //'post_date_gmt'  => [ Y-m-d H:i:s ] // The time post was made, in GMT.
			  //'comment_status' => [ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
			  //'post_category'  => [ array(<category id>, ...) ] // Default empty.
			  //'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
			  //'tax_input'      => [ array( <taxonomy> => <array | string> ) ] // For custom taxonomies. Default empty.
			  //'page_template'  => [ <string> ] // Default empty.
			);  
			
			$search_gcse_page_id = wp_insert_post( $search_gcse_page );

			$options['search_gcse_page_id'] = $search_gcse_page_id;
			
			$options['search_gcse_page_url'] = get_page_link( $search_gcse_page_id );
			
			//update_option( $option, $new_value );
			update_option( 'wgs_general_settings', $options );

		}

	}
	
	
	public function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	function plugin_url() {
		if ( $this->plugin_url ) return $this->plugin_url;
		return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	

	function wp_google_search_shortcode( $atts ){
		
		$options = get_option( 'wgs_general_settings' );

		//if ($options['use_default_correction_css'] == 1)
		//	wp_enqueue_style( 'wgs', plugins_url('wgs.css', __FILE__) );
		
		if ($options['searchbox_before_results'] == 1)
			$gcse_code = 'search';
		else
			$gcse_code = 'searchresults-only';

		$content  = '<div class="wgs_wrapper" id="wgs_wrapper_id">';
		//$content .= '<gcse:searchresults-only linktarget="_self"></gcse:searchresults-only>';
		
		//You can use HTML5-valid div tags as long as you observe these guidelines: //20140423
		//The class attribute must be set to gcse-XXX
		//Any attributes must be prefixed with data-.
		//$content .= '<gcse:' . $gcse_code . ' linktarget="_self"></gcse:' . $gcse_code . '>';
		$content .= '<div class="gcse-' . $gcse_code . '" data-linktarget="_self"></div>';
		
		$content .= '</div>';

		$content = apply_filters('wgs_shortcode_content', $content);
		
		return $content;
		
	}

	function wp_google_searchbox_shortcode( $atts ){
		
		$options = get_option( 'wgs_general_settings' );

		//if ($options['use_default_correction_css'] == 1)
		//	wp_enqueue_style( 'wgs', plugins_url('wgs.css', __FILE__) );

		$search_gcse_page_url = get_page_link( $options['search_gcse_page_id'] );
				
		$content  = '<div class="wgs_wrapper" id="wgs_widget_wrapper_id">';
		//You can use HTML5-valid div tags as long as you observe these guidelines: //20140423
		//The class attribute must be set to gcse-XXX
		//Any attributes must be prefixed with data-.
		//$content .= '<gcse:searchbox-only resultsUrl="' . $search_gcse_page_url . '"></gcse:searchbox-only>';
		$content .= '<div class="gcse-searchbox-only" data-resultsUrl="' . $search_gcse_page_url . '"></div>';
		
		$content .= '</div>';

		$content = apply_filters('wgs_searchbox_shortcode_content', $content);
		
		return $content;
		
	}
}

//Init WP_Google_Search class
$GLOBALS['wp_google_search'] = new WP_Google_Search();

}

?>