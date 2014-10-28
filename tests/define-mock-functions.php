<?php
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
