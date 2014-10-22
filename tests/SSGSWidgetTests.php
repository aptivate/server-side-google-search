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
		$this->assertEquals( $output, '<div class="ssgs-result-wrapper"></div>' );
	}

	public function test_before_widget_displayed(){
		$output = $this->get_widget_html(array(
			'before_widget' => '<p>Before text</p>',
			'after_widget' => '<p>After text</p>',
		));
		$heading = $this->get_html_elements_from_output( $output, 'p' );
		$this->assertEquals( (string)$heading[0], 'Before text' );
	}

	public function test_after_widget_displayed(){
		$output = $this->get_widget_html(array(
			'before_widget' => '<p>Before text</p>',
			'after_widget' => '<p>After text</p>',
		));
		$heading = $this->get_html_elements_from_output( $output, 'p' );
		$this->assertEquals( (string)$heading[1], 'After text' );
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
