<?php

require_once 'mock-file.php';

require_once 'wp-config.php';
require_once 'plugin.php';
require_once 'widgets.php';
require_once 'link-template.php';
require_once 'functions.php';

require_once 'mock-option.php';

require_once 'ssgs-widget.php';

require_once 'SSGSWidgetTestBase.php';

class SSGSWidgetTests extends SSGSWidgetTestBase
{
	public function test_creation(){
		$output = $this->get_widget_html();
		$this->assertEquals( '<div class="ssgs-result-wrapper"></div>', $output );
	}

	public function test_before_widget_displayed(){
		$output = $this->get_widget_html(array(
			'before_widget' => '<p>Before text</p>',
			'after_widget' => '<p>After text</p>',
		));
		$heading = $this->get_html_elements_from_output( $output, 'p' );
		$this->assertEquals( 'Before text', (string)$heading[0] );
	}

	public function test_after_widget_displayed(){
		$output = $this->get_widget_html(array(
			'before_widget' => '<p>Before text</p>',
			'after_widget' => '<p>After text</p>',
		));
		$heading = $this->get_html_elements_from_output( $output, 'p' );
		$this->assertEquals( 'After text', (string)$heading[1] );
	}

	public function to_upper( $content ) {
		return strtoupper( $content );
	}

	public function test_filter_applied(){
		add_filter( 'ssgs_widget_content', array( $this, 'to_upper' ) );

		$output = $this->get_widget_html();

		$this->assertEquals( '<DIV CLASS="SSGS-RESULT-WRAPPER"></DIV>', $output );
	}

	public function test_no_results() {
		$this->set_search_string( 'agroforestry Zambia' );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 0,
					),
				) ) ) );

		$output = $this->get_widget_html();
		$message = $this->get_html_element_from_output( $output, '/p/strong' );

		$this->assertEquals( 'Sorry, there were no results', $message );
	}

	public function test_search_string_displayed() {
		$this->set_search_string( "agroforestry' Zambia" );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 3,
					),
				)  ),
			'items' => array() ) );

		$output = $this->get_widget_html();
		$message = (string)$this->get_html_element_from_output( $output, '/h2' );

		$this->assertEquals( 'Search for ', $message );

		$message = (string)$this->get_html_element_from_output( $output, '/h2/strong' );

		$this->assertEquals( "agroforestry' Zambia", $message );
	}

	public function test_displays_error_on_api_failure() {
		$this->set_search_string( '' );
		global $_SSGS_MOCK_FILE_CONTENTS;

		$_SSGS_MOCK_FILE_CONTENTS = false;

		$output = $this->get_widget_html();
		$message = (string)$this->get_html_element_from_output( $output, '/p/strong' );

		$this->assertEquals( 'An error was encountered while performing the requested search',
			$message );
	}

	public function test_url_has_https_scheme() {
		$this->set_search_string( '' );

		$output = $this->get_widget_html();

		global $_SSGS_MOCK_FILE_URL;

		$scheme = parse_url( $_SSGS_MOCK_FILE_URL, PHP_URL_SCHEME );

		$this->assertEquals( 'https', $scheme );
	}

	public function test_url_has_host() {
		$this->set_search_string( '' );

		$output = $this->get_widget_html();

		global $_SSGS_MOCK_FILE_URL;

		$host = parse_url( $_SSGS_MOCK_FILE_URL, PHP_URL_HOST );

		$this->assertEquals( 'www.googleapis.com', $host );
	}

	public function test_api_version_defaults_to_v1() {
		$this->set_search_string( '' );

		$output = $this->get_widget_html();

		global $_SSGS_MOCK_FILE_URL;

		$path = parse_url( $_SSGS_MOCK_FILE_URL, PHP_URL_PATH );

		$this->assertEquals( '/customsearch/v1', $path );
	}

	public function test_api_key_passed_to_google_api() {
		$this->set_search_string( '' );
		$this->set_option( 'google_search_api_key',
			'dfkgjOoldsg3kKD6FSfkp7of9sjs8dofsdjosdfjA' );

		$this->get_widget_html();

		$key = $this->get_api_query_parameter( 'key' );
		$this->assertEquals( 'dfkgjOoldsg3kKD6FSfkp7of9sjs8dofsdjosdfjA',
			$key );
	}

	public function test_engine_id_passed_to_google_api() {
		$this->set_search_string( '' );
		$this->set_option( 'google_search_engine_id',
			'573285494839582010549:3ajfhsoghsak' );

		$this->get_widget_html();

		$key = $this->get_api_query_parameter( 'cx' );
		$this->assertEquals( '573285494839582010549%3A3ajfhsoghsak',
			$key );
	}

	public function test_format_defaults_to_json() {
		$this->set_search_string( '' );
		$this->get_widget_html();

		$format = $this->get_api_query_parameter( 'alt' );
		$this->assertThat( $format, $this->equalTo( 'json' ) );
	}

	public function test_sort_passed_to_google_api() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'sort', 'date' );
		$this->get_widget_html();

		$sort = $this->get_api_query_parameter( 'sort' );
		$this->assertThat( $sort, $this->equalTo( 'date' ) );
	}

	public function test_no_sort_parameter_by_default() {
		$this->set_search_string( '' );

		$this->get_widget_html();

		$format = $this->get_api_query_parameter( 'sort' );
		$this->assertThat( $format, $this->identicalTo( null ) );
	}

	public function test_items_per_page_passed_to_google_api() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'limit', 5 );

		$output = $this->get_widget_html();

		$num = $this->get_api_query_parameter( 'num' );
		$this->assertThat( $num, $this->equalTo( 5 ) );
	}

	public function test_items_per_page_default_is_10() {
		$this->set_search_string( '' );

		$output = $this->get_widget_html();

		$num = $this->get_api_query_parameter( 'num' );
		$this->assertThat( $num, $this->equalTo( 10 ) );
	}

	public function test_start_index_default_is_1() {
		$this->set_search_string( '' );

		$output = $this->get_widget_html();

		$start = $this->get_api_query_parameter( 'start' );
		$this->assertThat( $start, $this->equalTo( 1 ) );
	}

	public function test_start_index_read_from_query_string() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'start', 11 );

		$output = $this->get_widget_html();

		$start = $this->get_api_query_parameter( 'start' );
		$this->assertThat( $start, $this->equalTo( 11 ) );
	}

	// TODO - does this make any difference?
	public function test_prettyprint_defaults_to_true() {
		$this->set_search_string( '' );

		$output = $this->get_widget_html();

		$prettyprint = $this->get_api_query_parameter( 'prettyprint' );
		$this->assertThat( $prettyprint, $this->equalTo( 'true' ) );
	}

	public function test_search_string_passed_to_google_api() {
		$this->set_search_string( 'agroforestry zambia' );

		$output = $this->get_widget_html();

		$q = $this->get_api_query_parameter( 'q' );
		$this->assertThat( $q, $this->equalTo( 'agroforestry%2Bzambia' ) );
	}

	public function test_mock_response_returned_in_test_mode() {
		$this->set_search_string( '' );
		$this->set_option( 'results_source', 'test' );
		$this->get_widget_html();

		global $_SSGS_MOCK_FILE_URL;

		$this->assertThat( basename( $_SSGS_MOCK_FILE_URL ),
						   $this->equalTo( 'mock_results.json' ) );
	}

	public function test_total_items_restricted_to_100_for_free_edition() {
		$this->set_search_string( '' );
		$this->set_option( 'edition', 'free' );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1234,
					),
				)  ),
			'items' => array() ) );


		$output = $this->get_widget_html();

		$div = $this->get_html_element_from_output( $output, "/div[@class='ssgs-results-info']" );
		$this->assertEquals(
			'Displaying 0 items from around 100 matches',
			(string)$div );
	}

	public function test_total_items_unrestricted_for_paid_edition() {
		$this->set_search_string( '' );
		$this->set_option( 'edition', 'paid' );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1234,
					),
				)  ),
			'items' => array() ) );

		$output = $this->get_widget_html();

		$div = $this->get_html_element_from_output( $output, "/div[@class='ssgs-results-info']" );
		$this->assertEquals(
			'Displaying 0 items from around 1234 matches',
			(string)$div );

	}

	public function test_sort_by_date_highlighted() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'sort', 'date' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array() ) );

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/li[@class='ssgs-results-sort-date selected']/a" );
		$this->assertEquals(
			'Date',
			(string)$link );
	}

	public function test_sort_by_relevance_highlighted() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array() ) );

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/li[@class='ssgs-results-sort-relevance selected']/a" );
		$this->assertEquals(
			'Relevance',
			(string)$link );
	}

	public function test_relevance_link_has_empty_sort_parameter() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array() ) );

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/li[@class='ssgs-results-sort-relevance selected']/a" );
		$attributes = $link->attributes();
		$href = (string)$attributes['href'];

		$sort = $this->get_url_query_parameter( $href, 'sort' );
		$this->assertThat( $sort, $this->equalTo( '' ) );
	}

	public function test_date_link_has_sort_parameter() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array() ) );

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/li[@class='ssgs-results-sort-date']/a" );
		$attributes = $link->attributes();
		$href = (string)$attributes['href'];

		$sort = $this->get_url_query_parameter( $href, 'sort' );
		$this->assertThat( $sort, $this->equalTo( 'date' ) );
	}

	public function test_date_link_not_url_encoded() {
		$this->set_search_string( 'agroforestry Zambia' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array() ) );

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/li[@class='ssgs-results-sort-date']/a" );
		$attributes = $link->attributes();
		$href = (string)$attributes['href'];

		$sort = $this->get_url_query_parameter( $href, 's' );
		$this->assertThat( $sort, $this->equalTo( 'agroforestry+Zambia' ) );
	}

	public function test_thumbnail_read_from_metatags() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array(
				array(
					'pagemap' => array(
						'metatags' => array(
							array(
								'thumbnailurl' => 'thumbnailurl.png',
							),
						),
					),
				),
			),
		));

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/img[@class='ssgs-result-thumbnail']" );
		$attributes = $link->attributes();
		$href = (string)$attributes['src'];

		$this->assertThat( $href, $this->equalTo( 'thumbnailurl.png' ) );
	}

	public function test_thumbnail_read_from_cse_thumbnail() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array(
				array(
					'pagemap' => array(
						'cse_thumbnail' => array(
							array(
								'src' => 'cse_thumbnail.png',
							),
						),
					),
				),
			),
		));

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/img[@class='ssgs-result-thumbnail']" );
		$attributes = $link->attributes();
		$href = (string)$attributes['src'];

		$this->assertThat( $href, $this->equalTo( 'cse_thumbnail.png' ) );
	}

	public function test_thumbnail_read_from_cse_image() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array(
				array(
					'pagemap' => array(
						'cse_image' => array(
							array(
								'src' => 'cse_image.png',
							),
						),
					),
				),
			),
		));

		$output = $this->get_widget_html();
		$link = $this->get_html_element_from_output( $output,
													 "/img[@class='ssgs-result-thumbnail']" );
		$attributes = $link->attributes();
		$href = (string)$attributes['src'];

		$this->assertThat( $href, $this->equalTo( 'cse_image.png' ) );
	}

	public function test_default_thumbnail_used_if_none_other() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array(
				array(
					'pagemap' => array(
					),
				),
			),
		));

		$this->set_option( 'default_search_image_url',
			'default_image.png' );

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
													 "/img[@class='ssgs-result-thumbnail']" );
		$attributes = $link->attributes();
		$href = (string)$attributes['src'];

		$this->assertThat( $href, $this->equalTo( 'default_image.png' ) );
	}

	public function test_thumbnail_has_alt_text() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				)  ),
			'items' => array(
				array(
					'pagemap' => array(
					),
					'title' => 'Example title',
				),
			),
		));

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
													 "/img[@class='ssgs-result-thumbnail']" );
		$attributes = $link->attributes();
		$alt = (string)$attributes['alt'];

		$this->assertThat( $alt, $this->equalTo( 'Example title' ) );
	}

	public function test_link_displayed() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				) ),
			'items' => array(
				array(
					'link' => 'http://www.example.com/',
				),
			),
		));

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
													 "/div[@class='ssgs-result-header']/a" );

		$attributes = $link->attributes();
		$href = (string)$attributes['href'];

		$this->assertThat( $href, $this->equalTo( 'http://www.example.com/' ) );
	}

	public function test_title_displayed() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				) ),
			'items' => array(
				array(
					'link' => 'http://www.example.com/',
					'htmlTitle' => 'Example title',
				),
			),
		));

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
													 "/h3[@class='ssgs-result-title']/a" );

		$this->assertThat( (string)$link, $this->equalTo('Example title' ));

		$attributes = $link->attributes();
		$href = (string)$attributes['href'];

		$this->assertThat( $href, $this->equalTo( 'http://www.example.com/' ) );
	}

	public function test_formatted_url_in_result_description() {
		$this->set_option( 'show_urls', 'yes' );
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				) ),
			'items' => array(
				array(
					'htmlFormattedUrl' => 'http://www.example.com/',
				),
			),
		));

		$output = $this->get_widget_html();

		$p = $this->get_html_element_from_output( $output,
													 "/p[@class='ssgs-html-formatted-url']" );

		$this->assertThat( (string)$p, $this->equalTo('http://www.example.com/' ));
	}

	public function test_no_formatted_url_if_disabled() {
		$this->set_option( 'show_urls', 'no' );

		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				) ),
			'items' => array(
				array(
					'htmlFormattedUrl' => 'http://www.example.com/',
				),
			),
		));

		$output = $this->get_widget_html();

		$ps = $this->get_html_elements_from_output(
			$output,
			"/p[@class='ssgs-html-formatted-url']" );

		$this->assertThat( count( $ps ), $this->equalTo( 0 ) );
	}

	public function test_snippet_in_result_description() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				) ),
			'items' => array(
				array(
					'htmlSnippet' => 'Information about agroforestry',
				),
			),
		));

		$output = $this->get_widget_html();

		$span = $this->get_html_element_from_output( $output,
												"/span[@class='ssgs-html-snippet']" );

		$this->assertThat(
			(string)$span,
			$this->equalTo( 'Information about agroforestry' ) );
	}

	public function test_more_link_in_result_description() {
		$this->set_search_string( '' );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				) ),
			'items' => array(
				array(
					'link' => 'http://www.example.com/',
				),
			),
		));

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
												  "/a[@class='ssgs-expand']" );

		$this->assertThat( (string)$link,
						   $this->equalTo('[more]' ) );
		$attributes = $link->attributes();
		$href = (string)$attributes['href'];

		$this->assertThat( $href, $this->equalTo( 'http://www.example.com/' ) );
	}

	public function test_next_link_only_displayed_on_first_page() {
		$this->check_next_previous_links(array(
			'start' => 1,
			'total_results' => 1234,
			'expected_total' => 1234,
			'expected_next_start' => 11,
		));
	}

	public function test_next_link_uses_defined_limit() {
		$this->set_query_parameter( 'limit', 5 );

		$this->check_next_previous_links(array(
			'start' => 1,
			'total_results' => 1234,
			'expected_total' => 1234,
			'expected_next_start' => 6,
		));
	}

	public function test_link_preserves_custom_parameter() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'language', 'es' );
		$this->set_query_parameter( 'start', 11 );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1234,
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
													  "/a[@class='ssgs-prev']" );
		$attributes = $link->attributes();
		$href = (string)$attributes['href'];
		$this->assertThat( $this->get_url_query_parameter( $href, 'language' ),
						   $this->equalTo( 'es' ) );

	}

	public function test_link_parameter_sanitized() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'sort', '<script>alert("hello")</script>' );
		$this->set_query_parameter( 'start', 11 );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1234,
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
													  "/a[@class='ssgs-prev']" );
		$attributes = $link->attributes();
		$href = (string)$attributes['href'];
		$this->assertThat( $this->get_url_query_parameter( $href, 'sort' ),
						   $this->equalTo( '' ) );

	}

	public function test_previous_link_uses_defined_limit() {
		$this->set_query_parameter( 'limit', 5 );

		$this->check_next_previous_links(array(
			'start' => 11,
			'total_results' => 1234,
			'expected_total' => 1234,
			'expected_prev_start' => 6,
			'expected_next_start' => 16,
		));
	}

	public function test_next_previous_links_displayed_on_second_page() {
		$this->check_next_previous_links(array(
			'start' => 11,
			'total_results' => 1234,
			'expected_total' => 1234,
			'expected_prev_start' => 1,
			'expected_next_start' => 21,
		));
	}

	public function test_next_previous_links_displayed_on_third_page() {
		$this->check_next_previous_links(array(
			'start' => 21,
			'total_results' => 1234,
			'expected_total' => 1234,
			'expected_prev_start' => 11,
			'expected_next_start' => 31,
		));
	}

	public function test_previous_link_only_displayed_on_last_page() {
		$this->check_next_previous_links(array(
			'start' => 31,
			'total_results' => 40,
			'expected_total' => 40,
			'expected_prev_start' => 21,
		));
	}

	public function test_current_page_not_a_link() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'start', 11 );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 123,
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		$span = $this->get_html_element_from_output( $output,
													 "/span[@class='ssgs-page']" );
		$this->assertThat( (string)$span, $this->equalTo( 2 ) );
	}

	public function test_there_is_a_link_to_next_page() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'start', 1 );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 123,
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		$pages = $this->get_html_elements_from_output( $output,
													   "/*[@class='ssgs-page']" );
		$this->assertThat( (string)$pages[1], $this->equalTo( 2 ) );
		$attributes = $pages[1]->attributes();
		$href = (string)$attributes['href'];

		$this->assertThat( $this->get_url_query_parameter( $href, 'totalItems' ),
						   $this->equalTo( 123 ) );

		$this->assertThat( $this->get_url_query_parameter( $href, 'start' ),
						   $this->equalTo( 11 ) );
	}

	public function test_maximum_of_ten_pages_displayed() {
		$this->set_search_string( '' );
		$this->set_option( 'edition', 'paid' );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 123,
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		$pages = $this->get_html_elements_from_output( $output,
													   "/*[@class='ssgs-page']" );
		$this->assertThat( count( $pages ), $this->equalTo( 10 ) );
	}

	public function test_correct_pages_shown_for_limit() {
		$this->set_search_string( '' );
		$this->set_option( 'edition', 'paid' );
		$this->set_query_parameter( 'limit', 5 );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 50,
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		$pages = $this->get_html_elements_from_output( $output,
													   "/*[@class='ssgs-page']" );
		$this->assertThat( count( $pages ), $this->equalTo( 10 ) );
	}

	public function test_current_page_in_middle_of_pages() {
		$this->set_search_string( '' );
		$this->set_option( 'edition', 'paid' );
		$this->set_query_parameter( 'start', 101 ); // Page 11

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 200,
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		// expect pages to be 6 7 8 9 10 11 12 13 14 15

		$pages = $this->get_html_elements_from_output( $output,
													   "/*[@class='ssgs-page']" );
		$this->assertThat( (string)$pages[0], $this->equalTo( 6 ) );
		$this->assertThat( (string)$pages[9], $this->equalTo( 15 ) );

	}

	public function test_search_string_is_trimmed() {
		$this->set_search_string( '   agroforestry Zambia  ' );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 0,
					),
				) ) ) );
		$this->get_widget_html();

		$this->assertThat(
			$this->get_api_query_parameter( 'q' ),
			$this->equalTo( 'agroforestry%2BZambia' ));
	}
	
	public function test_filter_adds_content() {
		$item = array(
			'pagemap' => 'test_filter_adds_content',
			'last_modified' =>  date( 'd m Y' )
		);
		
		$this->set_search_string( '' );

		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => 1,
					),
				) ),
			'items' => array($item),
		));
		
		
		add_filter('ssgs-add-post-search-metadata', array($this, 'mock_filtering_content'), 10, 2);
		$output = $this->get_widget_html();

		$content .= '%s was run on %s';

		$this->assertNotSame(false, strpos($output, sprintf($content, $item['pagemap'], $item['last_modified'])));
	}
	
	public function mock_filtering_content($content, $item_data) {
		$content .= '%s was run on %s';
		return sprintf($content, $item_data['pagemap'], $item_data['last_modified']);
	}

	private function check_next_previous_links( $args ) {
		$defaults = array(
			'total_results' => null,
			'start' => null,
			'expected_total' => null,
			'expected_prev_start' => false,
			'expected_next_start' => false,
		);

		array_merge( $defaults, $args );

		$this->set_search_string( '' );
		$this->set_query_parameter( 'start', $args['start'] );
		$this->set_search_results( array(
			'queries' => array(
				'request' => array(
					array(
						'totalResults' => $args['total_results'],
					),
				) ),
			'items' => array(),
		));

		$output = $this->get_widget_html();

		$links = $this->get_html_elements_from_output( $output,
													 "/a[@class='ssgs-prev']" );

		if ( $args['expected_prev_start'] ) {
			$attributes = $links[0]->attributes();
			$href = (string)$attributes['href'];

			$this->assertThat( $this->get_url_query_parameter( $href, 'totalItems' ),
							   $this->equalTo( $args['expected_total'] ) );

			$this->assertThat( $this->get_url_query_parameter( $href, 'start' ),
							   $this->equalTo( $args['expected_prev_start'] ) );
		} else {
			$this->assertThat( count( $links ), $this->equalTo( 0 ) );
		}

		$links = $this->get_html_elements_from_output( $output,
													   "/a[@class='ssgs-next']" );

		if ( $args['expected_next_start'] ) {
			$attributes = $links[0]->attributes();
			$href = (string)$attributes['href'];

			$this->assertThat( $this->get_url_query_parameter( $href, 'totalItems' ),
							   $this->equalTo( $args['expected_total'] ) );

			$this->assertThat( $this->get_url_query_parameter( $href, 'start' ),
							   $this->equalTo( $args['expected_next_start'] ) );
		} else {
			$this->assertThat( count( $links ), $this->equalTo( 0 ) );
		}
	}

	private function get_api_query_parameter( $name ) {
		global $_SSGS_MOCK_FILE_URL;

		return $this->get_url_query_parameter( $_SSGS_MOCK_FILE_URL, $name );
	}

	private function get_url_query_parameter( $url, $name ) {
		$params = $this->get_url_query_parameters( $url );

		return $params[ $name ];
	}

	private function get_url_query_parameters( $url ) {
		$query = parse_url( $url, PHP_URL_QUERY );

		$param_pairs = explode( '&', $query );

		$params = array();
		foreach ( $param_pairs as $param_pair ) {
			$pieces = explode( '=', $param_pair );

			$params[ $pieces[0] ] = $pieces[1];
		}

		return $params;
	}

	private function set_search_string( $search_string ) {
		$this->set_query_parameter( 's', $search_string );
	}

	private function set_query_parameter( $name, $value ) {
		$_GET[ $name ] = $value;
	}

	private function set_option( $name, $value ) {
		global $_SSGS_MOCK_OPTIONS;

		$_SSGS_MOCK_OPTIONS[ $name ] = $value;
	}

	private function get_widget_html( $args = array() ){
		$widget = new SSGS_Widget();
		$defaults = array(
			'before_widget' => '',
			'after_widget' => '',
		);
		$args = array_merge( $defaults, $args );

		$instance = null;
		ob_start();
		$widget->widget( $args, $instance );
		$output = ob_get_contents();
		$this->assertTrue( ob_end_clean() );

		return $output;
	}

	private function set_search_results( $results ) {
		global $_SSGS_MOCK_FILE_CONTENTS;

		$_SSGS_MOCK_FILE_CONTENTS = json_encode( $results );
	}
}
