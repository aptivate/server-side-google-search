<?php
class SSGSWidgetTestBase extends PHPUnit_Framework_TestCase
{
	protected function get_html_element_from_output( $output, $path )
	{
		$elements = $this->get_html_elements_from_output( $output, $path );

		$this->assertThat( count( $elements ), $this->equalTo( 1 ) );

		return $elements[0];
	}

	protected function get_html_elements_from_output( $output, $path ) {

		$html = "<html>$output</html>";

		// Not ideal but the XML parser doesn't like HTML entities
		$entities = array(
			'agrave',
			'copy',
			'eacute',
		);

		foreach ( $entities as $entity ) {
			$html = str_replace( "&$entity;", '', $html );
		}

		$use_errors = libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $html );
		if ( $xml === false ) {
			echo "HTML start:\n$html\nHTML end\n";

			foreach ( libxml_get_errors() as $error ) {
				echo "\t", $error->message;
			}

			libxml_clear_errors();
			$this->assertTrue( $xml !== false, 'Failed to parse html' );
		}

		libxml_use_internal_errors( $use_errors );

		$elements = $xml->xpath( "/html/$path" );
		$this->assertTrue(($elements !== false),
		"There was a problem evaluating the xpath $path");

		return $elements;
	}
}