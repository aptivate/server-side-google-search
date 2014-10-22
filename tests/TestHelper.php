<?php
$path = array(
	'..',
	'../../../../wp-includes/',
	get_include_path(),
);
set_include_path( implode( PATH_SEPARATOR, $path ) );
