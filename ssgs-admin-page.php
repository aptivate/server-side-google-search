<?php

class SSGS_Admin_Page {

	function __construct() {
		add_action( 'admin_menu', array($this, 'ssgs_admin_menu') ) ;
		add_action( 'admin_init', array($this, 'ssgs_admin_init') );
	}

	function ssgs_admin_menu () {
		add_options_page( __('Server-Side Google Search','ssgs'),__('Server-Side Google Search','ssgs')
			,'manage_options','ss-google-search', array($this, 'add_options_page_callback' ));
	}


	function ssgs_admin_init()
	{

		$this->ssgs_set_defaults();

		register_setting(
			'ssgs_general_settings', // Option group / tab page
			'ssgs_general_settings', // Option name
			array($this, 'sanitize') // Sanitize
		);

		add_settings_section(
			'ssgs_general_section', // ID
			__('General Settings','ssgs'), // Title //ML
			array($this,'print_section_info'), // Callback
			'ssgs_general_settings' // Page / tab page
		);

        add_settings_field(
            'edition', // ID
            __('Edition', 'ssgs'),
            array($this, 'posttype_callback'),
            'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
        );

		add_settings_field(
			'google_search_api_key', // ID
			__('Google Search API Key','ssgs'), // Title
			array($this, 'posttype_callback'), // Callback
			'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
		);

		add_settings_field(
			'google_search_engine_id', // ID
			__('Google Search Engine ID','ssgs'), // Title
			array($this, 'posttype_callback'), // Callback
			'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
		);

		add_settings_field(
			'default_search_image_url', // ID
			__('Default Search Image URL','ssgs'), // Title
			array($this, 'posttype_callback'), // Callback
			'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
		);
        add_settings_field(
            'results_source', // ID
            __('Results source', 'ssgs'),
            array($this, 'posttype_callback'),
            'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
        );

	}

	function ssgs_set_defaults() {

		$options = get_option( 'ssgs_general_settings' );

		$options = wp_parse_args( $options,
                                  array(
                                      'google_search_api_key' => '',
                                      'google_search_engine_id' => '',
                                      'edition' => 'free',
									  'results_source' => 'real',
                                      ) );

		update_option( 'ssgs_general_settings', $options );

	}

	function add_options_page_callback()
	{

		wp_enqueue_style( 'ssgs-admin', plugins_url('ssgs-admin.css', __FILE__) );

		?>
		<div class="wrap">
			<h2><?php _e('Server-Side Google Search by Aptivate, WebshopLogic','ssgs') ?></h2>

			<div style="float:left; width: 70%">

				<form method="post" action="options.php"><!--form-->

					<?php

					settings_fields( 'ssgs_general_settings' );
					$options = get_option( 'ssgs_general_settings' ); //option_name

					?>
					<h3><?php _e('General Settings','ssgs') ?></h3>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php echo __('Edition','ssgs') . ':' ?></th>
							<td>
								<?php
						        echo(
						            '<select id="edition" name="ssgs_general_settings[edition]">'
						        );

                                $edition_options = array(
                                    'free' => __('Free'),
                                    'paid' => __('Paid'),
                                );

                                foreach($edition_options as $value => $text) {
						            if ( esc_attr( $options['edition'] ) == $value) {
                                        $selected = ' selected';
                                    }
                                    else {
                                        $selected = '';
                                    }

                                    echo("<option value='$value'$selected>$text</option>");
                                }

                                echo('</select>');

								echo '<br /><span class="description">' . __('The free edition of Google Search is limited to 100 results per search and 100 searches per day', 'ssgs')
							    ?>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php echo __('Google Search Engine ID','ssgs') . ':' ?></th>
							<td>
								<?php
						        printf(
						            '<input type="text" id="google_search_engine_id" name="ssgs_general_settings[google_search_engine_id]" value="%s" size="50" />',
						            esc_attr( $options['google_search_engine_id'])
						        );
								echo '<br /><span class="description">' . __('Register to Google Custom Search Engine and get your Google Search Engine ID here: ','ssgs') . '<a href="https://www.google.com/cse/" target="_blank">https://www.google.com/cse/</a>' . '</span>';
								echo '<br /><span class="description">' . __('You will get a Google Search Engine ID like this: 012345678901234567890:0ijk_a1bcde','ssgs') . '</span>';
								echo '<br /><span class="description">' . __('Enter this Google Search Engine ID here.','ssgs') . '</span>';

							    ?>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php echo __('Google Search API Key','ssgs') . ':' ?></th>
							<td>
								<?php
						        printf(
						            '<input type="text" id="google_search_api_key" name="ssgs_general_settings[google_search_api_key]" value="%s" size="50" />',
						            esc_attr( $options['google_search_api_key'])
						        );
							    ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php echo __('Default Search Image URL','ssgs') . ':' ?></th>
							<td>
								<?php
						        printf(
						            '<input type="text" id="default_search_image_url" name="ssgs_general_settings[default_search_image_url]" value="%s" size="50" />',
						            esc_attr( $options['default_search_image_url'])
						        );
							    ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php echo __('Results source','ssgs') . ':' ?></th>
							<td>
								<?php
						        echo(
						            '<select id="results_source" name="ssgs_general_settings[results_source]">'
						        );

                                $source_options = array(
                                    'real' => __('Real'),
                                    'test' => __('Test'),
                                );

                                foreach($source_options as $value => $text) {
						            if ( esc_attr( $options['results_source'] ) == $value) {
                                        $selected = ' selected';
                                    }
                                    else {
                                        $selected = '';
                                    }

                                    echo("<option value='$value'$selected>$text</option>");
                                }

                                echo('</select>');

								echo '<br /><span class="description">' . __('Use real results from Google or test data (useful for developers)', 'ssgs')
							    ?>
							</td>
						</tr>
					</table>


					<?php
					submit_button();
					?>

				</form><!--end form-->

			</div><!--emd float:left; width: 70% / 100% -->

		</div>
		<?php

	}

	function sanitize( $input )
	{
		if( !is_numeric( $input['id_number'] ) )
			$input['id_number'] = '';

		if( !empty( $input['title'] ) )
			$input['title'] = sanitize_text_field( $input['title'] );

		return $input;
	}
}
