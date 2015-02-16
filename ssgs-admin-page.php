<?php

class SSGS_Admin_Page {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'ssgs_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'ssgs_admin_init' ) );
	}

	function ssgs_admin_menu() {
		add_options_page(
			__( 'Server-Side Google Search', 'ssgs' ),
			__( 'Server-Side Google Search', 'ssgs' ),
			'manage_options',
			'ss-google-search',
			array( $this, 'add_options_page_callback' ));
	}


	function ssgs_admin_init() {
		$this->ssgs_set_defaults();

		register_setting(
			'ssgs_general_settings',
			'ssgs_general_settings'
		);
	}

	function ssgs_set_defaults() {
		$options = get_option( 'ssgs_general_settings' );

		$options = wp_parse_args( $options,
									array(
										'edition' => 'free',
										'google_search_api_key' => '',
										'google_search_engine_id' => '',
										'results_source' => 'real',
										'show_urls' => 'yes',
										) );

		update_option( 'ssgs_general_settings', $options );
	}

	function add_options_page_callback() {
		?>
		<div class="wrap">
		<h2><?php _e( 'Server-Side Google Search by Aptivate', 'ssgs' ) ?></h2>

		<div>

		<form method="post" action="options.php">

<?php
		settings_fields( 'ssgs_general_settings' );
		$options = get_option( 'ssgs_general_settings' );

		?>
		<h3><?php _e( 'General Settings', 'ssgs' ) ?></h3>

		<table class="form-table">
		<tr valign="top">
		<th scope="row"><?php echo __( 'Edition', 'ssgs' ) . ':' ?></th>
		<td>
<?php
		echo(
			'<select id="edition" name="ssgs_general_settings[edition]">'
		);

		$edition_options = array(
			'free' => __( 'Free' ),
			'paid' => __( 'Paid' ),
		);

		foreach ( $edition_options as $value => $text ) {
			if ( esc_attr( $options['edition'] ) == $value ) {
				$selected = ' selected';
			}
			else {
				$selected = '';
			}

			echo("<option value='$value'$selected>$text</option>");
		}

		echo('</select>');

		echo '<br /><span class="description">' . __( 'The free edition of Google Search is limited to 100 results per search and 100 searches per day', 'ssgs' )
			?>
		</td>
		</tr>


		<tr valign="top">
		<th scope="row"><?php echo __( 'Google Search Engine ID', 'ssgs' ) . ':' ?></th>
		<td>
<?php
		printf(
			'<input type="text" id="google_search_engine_id" name="ssgs_general_settings[google_search_engine_id]" value="%s" size="50" />',
			esc_attr( $options['google_search_engine_id'] )
		);
		echo '<br /><span class="description">' . __( 'Register with Google Custom Search Engine and get your Google Search Engine ID here: ', 'ssgs' ) . '<a href="https://www.google.com/cse/" target="_blank">https://www.google.com/cse/</a>' . '</span>';
		echo '<br /><span class="description">' . __( 'You will get a Google Search Engine ID like this: 012345678901234567890:0ijk_a1bcde','ssgs' ) . '</span>';
		echo '<br /><span class="description">' . __( 'Enter this Google Search Engine ID here.', 'ssgs' ) . '</span>';

		?>
		</td>
		</tr>

		<tr valign="top">
		<th scope="row"><?php echo __( 'Google Search API Key','ssgs' ) . ':' ?></th>
		<td>
<?php
		printf(
			'<input type="text" id="google_search_api_key" name="ssgs_general_settings[google_search_api_key]" value="%s" size="50" />',
			esc_attr( $options['google_search_api_key'] )
		);
		?>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row"><?php echo __( 'Default Search Image URL', 'ssgs' ) . ':' ?></th>
		<td>
<?php
		printf(
			'<input type="text" id="default_search_image_url" name="ssgs_general_settings[default_search_image_url]" value="%s" size="50" />',
			esc_attr( $options['default_search_image_url'] )
		);
		?>
		</td>
		</tr>

		<tr valign="top">
		<th scope="row"><?php echo __( 'Display URLs in search results', 'ssgs' ) . ':' ?></th>
		<td>
<?php
		echo(
			'<select id="show_urls" name="ssgs_general_settings[show_urls]">'
		);

		$url_options = array(
			'yes' => __( 'Yes' ),
			'no' => __( 'No' ),
		);

		foreach ( $url_options as $value => $text ) {
			if ( esc_attr( $options['show_urls'] ) == $value ) {
				$selected = ' selected';
			}
			else {
				$selected = '';
			}

			echo("<option value='$value'$selected>$text</option>");
		}

		echo('</select>');
			?>
		</td>
		</tr>

		<tr valign="top">
		<th scope="row"><?php echo __( 'Results source', 'ssgs' ) . ':' ?></th>
		<td>
<?php
		echo(
			'<select id="results_source" name="ssgs_general_settings[results_source]">'
		);

		$source_options = array(
			'real' => __( 'Real' ),
			'test' => __( 'Test' ),
		);

		foreach ( $source_options as $value => $text ) {
			if ( esc_attr( $options['results_source'] ) == $value ) {
				$selected = ' selected';
			}
			else {
				$selected = '';
			}

			echo("<option value='$value'$selected>$text</option>");
		}

		echo('</select>');

		echo '<br /><span class="description">' . __( 'Use real results from Google or test data (useful for developers)', 'ssgs' )
			?>
		</td>
		</tr>
		</table>


<?php
		submit_button();
		?>

		</form>

			  </div>

											</div>
<?php

	}
}
