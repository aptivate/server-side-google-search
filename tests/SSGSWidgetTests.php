<?php

require_once 'mock-file.php';

require_once 'wp-config.php';
require_once 'plugin.php';
require_once 'widgets.php';
require_once 'link-template.php';
require_once 'functions.php';

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
		$_GET['s'] = 'agroforestry Zambia';

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
		$_GET['s'] = 'agroforestry Zambia';

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
