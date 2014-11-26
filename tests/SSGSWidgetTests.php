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
		$this->set_search_string( 'agroforestry Zambia' );

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

		$this->assertEquals( 'agroforestry Zambia', $message );
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

	public function test_url_contains_api_version() {
		$this->set_search_string( '' );
		$this->set_query_parameter(	'v', 'v2' );

		$output = $this->get_widget_html();

		global $_SSGS_MOCK_FILE_URL;

		$path = parse_url( $_SSGS_MOCK_FILE_URL, PHP_URL_PATH );

		$this->assertEquals( '/customsearch/v2', $path );
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
		$this->assertEquals( '573285494839582010549:3ajfhsoghsak',
			$key );
	}

	public function test_format_defaults_to_json() {
		$this->set_search_string( '' );
		$this->get_widget_html();

		$format = $this->get_api_query_parameter( 'alt' );
		$this->assertThat( $format, $this->equalTo( 'json' ) );
	}

	public function test_format_passed_to_google_api() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'form', 'xml' );
		$this->get_widget_html();

		$format = $this->get_api_query_parameter( 'alt' );
		$this->assertThat( $format, $this->equalTo( 'xml' ) );
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
		$this->assertThat( $q, $this->equalTo( 'agroforestry+zambia' ) );
	}

	public function test_facet_passed_to_google_api() {
		$this->set_search_string( '' );
		$this->set_query_parameter( 'facet', 'adaptation' );
		$this->get_widget_html();

		$hq = $this->get_api_query_parameter( 'hq' );
		$this->assertThat( $hq, $this->equalTo( 'adaptation' ) );
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

	public function test_modified_date_displayed() {
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
					'metatags-modified-date' => '26th November 2014',
				),
			),
		));

		$output = $this->get_widget_html();

		$link = $this->get_html_element_from_output( $output,
													 "/span[@class='ssgs-modified-date']" );
		$this->assertThat( (string)$link, $this->equalTo( '26th November 2014' ) );
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

		$p = $this->get_html_element_from_output( $output,
												  "/span[@class='ssgs-html-snippet']" );

		$this->assertThat( (string)$p,
						   $this->equalTo('Information about agroforestry' ) );
	}

	public function test_date_snippet_in_result_description() {
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
					'metatags-modified-date' => '26th November 2014',
				),
			),
		));

		$output = $this->get_widget_html();

		$p = $this->get_html_element_from_output( $output,
												  "/span[@class='ssgs-modified-date']" );

		$this->assertThat( (string)$p,
						   $this->equalTo('26th November 2014' ) );
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
