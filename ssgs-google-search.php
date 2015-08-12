<?php
/*
  Plugin Name: Server-Side Google Search
  Plugin URI: https://github.com/aptivate/server-side-google-search
  Description: Google Custom Search for your site, without JavaScript
  Version: 1.0.3
  Author: Aptivate
  Author URI: http://www.aptivate.org/
  License: GPLv2 or later
  Text Domain: ssgs
  Domain Path: /languages/
  Requires at least: 3.7
  Tested up to: 4.3
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
			load_plugin_textdomain( 'ssgs', false,
									dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			global $ssgs_admin_page;
			$ssgs_admin_page = new SSGS_Admin_Page;

			$deps = array();
			$css_version = 3;
			wp_enqueue_style( 'ssgs', plugins_url( 'ssgs.css', __FILE__ ),
								$deps, $css_version );

			do_action( 'ssgs_init', $this );
		}

		public function plugin_path() {
			if ( $this->plugin_path ) {
				return $this->plugin_path;
			}

			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		function plugin_url() {
			if ( $this->plugin_url ) {
				return $this->plugin_url;
			}
			return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
		}
	}

	$GLOBALS['ss_google_search'] = new SS_Google_Search();
}
?>
