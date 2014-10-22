<?php
function __( $text ) {
    return $text;
}

function wp_parse_args( $args, $defaults = '' ) {
}

function get_option( $option ) {
	return array(
		'google_search_api_key' => '',
		'google_search_engine_id' => '',
		'results_source' => '',
	);
}
