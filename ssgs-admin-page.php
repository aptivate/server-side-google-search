<?php

class SSGS_Admin_Page {

	function __construct() {
		add_action( 'admin_menu', array($this, 'ssgs_admin_menu') ) ;
		add_action( 'admin_init', array($this, 'ssgs_admin_init') );
	}

	function ssgs_admin_menu () {

		//add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
		add_options_page( __('Server-Side Google Search','ssgs'),__('Server-Side Google Search','ssgs')
			,'manage_options','wp-google-search', array($this, 'add_options_page_callback' ));
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
			'searchbox_before_results', // ID
			__('Display search box before search results','ssgs'), // Title
			array($this, 'posttype_callback'), // Callback
			'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
		);

		add_settings_field(
			'use_default_correction_css', // ID
			__('Use default corrections CSS','ssgs'), // Title
			array($this, 'posttype_callback'), // Callback
			'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
		);

        add_settings_field( //HIDDEN
            'search_gcse_page_id', // ID
            'search_gcse_page_id', // Title
			array($this, 'posttype_callback'), // Callback
			'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
        );

        add_settings_field( //HIDDEN
            'search_gcse_page_url', // ID
            'search_gcse_page_url', // Title
			array($this, 'posttype_callback'), // Callback
			'ssgs_general_settings', // Page / tab page
			'ssgs_general_section' // Section
        );

	}

	function ssgs_set_defaults() {

		$options = get_option( 'ssgs_general_settings' );

		$options = wp_parse_args( $options, array(
                        'google_search_api_key' => '',
			'google_search_engine_id' => '',
			'searchbox_before_results' => '0',
			'use_default_correction_css' => '1',
		) );

		update_option( 'ssgs_general_settings', $options );

	}

	function add_options_page_callback()
	{

		wp_enqueue_style( 'ssgs-admin', plugins_url('ssgs-admin.css', __FILE__) );

		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('Server-Side Google Search by Aptivate, WebshopLogic','ssgs') ?></h2>

			<div style="float:left; width: 70%">

				<form method="post" action="options.php"><!--form-->

					<?php

					settings_fields( 'ssgs_general_settings' );
					$options = get_option( 'ssgs_general_settings' ); //option_name

					?>
					<h3><?php _e('General Settings','ssgs') ?></h3>
					<?php
					//echo __('Enter your settings below','ssgs') . ':'
					?>

					<table class="form-table">

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
							<th scope="row"><?php echo __('Display search box before search results','ssgs') . ':' ?></th>
							<td>
								<?php
								printf(
									'<input type="hidden" name="ssgs_general_settings[searchbox_before_results]" value="0"/>
									<input type="checkbox" id="searchbox_before_results" name="ssgs_general_settings[searchbox_before_results]"
									value="1"' . checked( 1, esc_attr( $options['searchbox_before_results']), false ) . ' />'
								);
								echo '<br /><span class="description">' . __('If this option is turned on, the search field will appear above the search results.','ssgs') . '</span>';

								?>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php echo __('Use default corrections CSS','ssgs') . ':' ?></th>
							<td>
								<?php
								printf(
									'<input type="hidden" name="ssgs_general_settings[use_default_correction_css]" value="0"/>
									<input type="checkbox" id="use_default_correction_css" name="ssgs_general_settings[use_default_correction_css]"
									value="1"' . checked( 1, esc_attr( $options['use_default_correction_css']), false ) . ' />'
								);
								echo '<br /><span class="description">' . __('If this option is turned on, some css will be applied to improve the appearance of search elements in case of most WordPress themes.','ssgs') . '</span>';

								?>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php echo __('Search Page Target URL','ssgs') . ':' ?></th>

							<td>

								<?php
						        printf(
						            '<input type="hidden" id="search_gcse_page_id" name="ssgs_general_settings[search_gcse_page_id]" value="%s" />',
						            esc_attr( $options['search_gcse_page_id'])
								);

						        printf(
						            '<input type="text" id="search_gcse_page_url" name="ssgs_general_settings[search_gcse_page_url]" value="%s" size="50" disabled />',
						            esc_attr( get_page_link( $options['search_gcse_page_id'] ))
								);

								echo '<br /><span class="description">' . __('The plugin automatically generated a page for displaying search results. You can see here the URL of this page. Please do not delete this page and do not change the permalink of it!','ssgs') . '</span>';
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

	//delete ssgs options
	//delete from em_options where option_name like 'ssgs_gen%'


}