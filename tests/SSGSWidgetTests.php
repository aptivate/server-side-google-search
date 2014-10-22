<?php

require_once 'plugin.php';
require_once 'widgets.php';
require_once 'mock-functions.php';
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
}
