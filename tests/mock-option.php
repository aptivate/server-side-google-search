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
			throw new Exception( "Unexpected option: '$option'" );
	}
}

$mock_function_args = array(
	'get_option' => '$option,$default = false',
);

foreach ( $mock_function_args as $original_name => $args ) {
	/* Hack to allow the staging server to support the mock login without having
	 * to install runkit on it (apd returns bogus errors during testing so we
	 * prefer runkit).
	 */

	if ( function_exists( 'runkit_function_copy' ) ) {
		if ( ! runkit_function_copy( $original_name, "real_$original_name" ) ) {
			throw new Exception( "Failed to copy $original_name" );
		}

		if ( ! runkit_function_redefine( $original_name, $args, "return mock_$original_name($args);" ) ) {
			throw new Exception( "Failed to redefine $original_name" );
		}
	}
	else
	{
		$ok = override_function( $original_name, $args, "return mock_$original_name( $args );" );
		if ( ! $ok ) {
			echo "Failed to override $original_name with mock_$original_name";
		}

		$ok = rename_function( '__overridden__', "dummy_$original_name" );
		if ( ! $ok ) {
			echo "Failed to rename __overridden__ with $ren_func";
		}
	}
}
?>
