<?php
global $_SSGS_MOCK_FILE_CONTENTS;
global $_SSGS_MOCK_FILE_URL;

function mock_file_get_contents( $url ) {
	global $_SSGS_MOCK_FILE_CONTENTS;
	global $_SSGS_MOCK_FILE_URL;

	$_SSGS_MOCK_FILE_URL = $url;

	return $_SSGS_MOCK_FILE_CONTENTS;
}

$mock_function_args = array(
	'file_get_contents' => '$url',
);

include 'define-mock-functions.php';
