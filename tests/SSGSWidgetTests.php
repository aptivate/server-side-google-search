<?php

require_once 'plugin.php';
require_once 'widgets.php';
require_once 'mock-functions.php';
require_once 'ssgs-widget.php';

class SSGSWidgetTests extends PHPUnit_Framework_TestCase
{
	public function test_creation(){
		$widget = new SSGS_Widget();
		$args = array();
		$instance = null;

		$output = $this->get_widget_html();
		$this->assertEquals( $output, '<div class="ssgs-result-wrapper"></div>' );
	}

	private function get_widget_html(){
		$widget = new SSGS_Widget();
		$args = array(
			'before_widget' => '',
			'after_widget' => '',
		);
		$instance = null;
		ob_start();
		$widget->widget( $args, $instance );
		$output = ob_get_contents();
		$this->assertTrue( ob_end_clean() );

		return $output;
	}
}
