<?php
global $_SSGS_MOCK_OPTIONS;

$_SSGS_MOCK_OPTIONS = array();

function mock_get_option( $option, $default = false ) {
	switch ( $option ) {
		case 'ssgs_general_settings':
			$defaults = array(
				'google_search_api_key' => '',
				'google_search_engine_id' => '',
				'results_source' => '',
				'edition' => '',
				'default_search_image_url' => '',
			);

			global $_SSGS_MOCK_OPTIONS;

			$options = array_merge( $defaults, $_SSGS_MOCK_OPTIONS );

			return $options;

		case 'home':
			return 'http://test.localhost';

		default:
			return "Mock option: $option";
	}
}

$mock_function_args = array(
	'get_option' => '$option,$default = false',
);

include 'define-mock-functions.php';
