<?php
class SSGS_Widget extends WP_Widget {
    function __construct() {
        parent::WP_Widget(false, $name = __('Server-Side Google Search (SSGS)','ssgs'));

    }


    function widget($args, $instance) {
        global $ssgs;

        echo $args['before_widget'];

        $content  = '<div class="ssgs_result_wrapper">';
        $content .= $this->get_search_results();
        $content .= '</div>';

        echo apply_filters('ssgs_widget_content', $content);

        echo $args['after_widget'];
    }

    /**
     * Originally taken from:
     * https://github.com/jasonclark/digital-collections-custom-search-api
     *
     * LICENSE:
     *
     * The MIT License (MIT)
     *
     * Copyright (c) 2013, Montana State University (MSU) Library
     *
     * Permission is hereby granted, free of charge, to any person obtaining a
     * copy of this software and associated documentation files (the
     * "Software"), to deal in the Software without restriction, including
     * without limitation the rights to use, copy, modify, merge, publish,
     * distribute, sublicense, and/or sell copies of the Software, and to
     * permit persons to whom the Software is furnished to do so, subject to
     * the following conditions:
     *
     * The above copyright notice and this permission notice shall be included
     * in all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
     * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
     * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
     * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
     * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
     * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
     * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
     *
     */

    private function get_search_results() {
        $options = get_option( 'ssgs_general_settings' );

	// Number of records to display per page (1 - 10)
	$recordsPerPage = 10;

	// Set default value for query
	$q = isset($_GET['s']) ? urlencode(strip_tags(trim($_GET['s']))) : null;

	// Set default value for API format
	$form = isset($_GET['form']) ? htmlentities(strip_tags($_GET['form'])) : 'json';

	// Set default value for page length (number of entries to display)
	$limit = isset($_GET['limit']) ? strip_tags((int)$_GET['limit']) : "$recordsPerPage";

	// Set default value for page start index
	$start = isset($_GET['start']) ? strip_tags((int)$_GET['start']) : '1';

	// Set default value for facet browse
	$facet = isset($_GET['facet']) ? htmlentities(strip_tags($_GET['facet'])) : null;

	// Set default value for results sorting
	$sort = isset($_GET['sort']) ? htmlentities(strip_tags($_GET['sort'])) : null;

	// Set API version for Google Custom Search API
	$v = isset($_GET['v']) ? strip_tags((int)$_GET['v']) : 'v1';

	// Set user API key for Google Custom Search API
	$api_key = $options['google_search_api_key'];

	// Set user ID for Google custom search engine
	$id = $options['google_search_engine_id'];

	if (!is_null($q)) {
		// Process query

		// Set URL for the Google Custom Search API call
		$url = "https://www.googleapis.com/customsearch/$v?key=$api_key&cx=$id&alt=$form".(is_null($sort) ? "" : "&sort=$sort")."&num=$limit&start=$start&prettyprint=true&q=$q".(is_null($facet) ? "" : "&hq=$facet");

		// Build request and send to Google Ajax Search API
		$request = file_get_contents($url);

		if ($request === FALSE) {
			// API call failed, display message to user
			return '<p><strong>' . __('An error was encountered while performing the requested search', 'ssgs') . '</strong></p>'."\n";
		}

		// Decode json object(s) out of response from Google Ajax Search API
		$result = json_decode($request, true);

		// Get values in json data for number of search results returned
		$totalItems = isset($_GET['totalItems']) ?  strip_tags((int)$_GET['totalItems']) : $result['queries']['request'][0]['totalResults'];
		if ($totalItems <= 0) {
			// Empty results, display message to user
			$content = '<p><strong>' . __('Sorry, there were no results', 'ssgs') ."</strong></p>\n";
	    }
		else {
			// Make sure some results were returned, show results as html with result numbering and pagination

			$parsed_url = parse_url($_SERVER['REQUEST_URI']);

			$results_displayed = count($result['items']);

			$content = '<h2 class="ssgs_result_page_title">' . __('Search for', 'ssgs'). ' <strong>'.
				stripslashes(urldecode($q)) . "</strong> (" .
                sprintf(__('Displaying %d items from around %d matches', 'ssgs'), $results_displayed, $totalItems) . ") </h2>" .
				'<div class="result-facet">';

			$relevance_url = $this->build_url($parsed_url, array(
				'sort' => ''));
			$date_url = $this->build_url($parsed_url, array(
				'sort' => 'date'));

			$date_classes = array('ssgs_results_sort_date');
			$relevance_classes = array('ssgs_results_sort_relevance');

			if ($sort == 'date') {
				$date_classes[] = 'selected';
			} else {
				$relevance_classes[] = 'selected';
			}

			$date_classes = implode(' ', $date_classes);
			$relevance_classes = implode(' ', $relevance_classes);

			$content .= '<ul class="facet-filter facet">' .
				"<li class='$relevance_classes'><span class='facet-heading'>" . __('Sort', 'ssgs') . "</span><a class='facet-link facet' href='$relevance_url'>" . __('Relevance', 'ssgs'). "</a></li>
                <li class='$date_classes'><a class='facet-link facet' href='$date_url'>" . __('Date', 'ssgs') . "</a></li>
                </ul>";

			$content .= '<ul class="ssgs_result_list">';

            $options = get_option('ssgs_general_settings');

			foreach ($result['items'] as $item) {
				$link = rawurldecode($item['link']);

				if (isset($item['pagemap']['metatags'][0]['thumbnailurl'])) {
					$thumbnail = $item['pagemap']['metatags'][0]['thumbnailurl'];
				}
				elseif(isset($item['pagemap']['cse_thumbnail'][0]['src'])) {
					$thumbnail = $item['pagemap']['cse_thumbnail'][0]['src'];
				}
				elseif(isset($item['pagemap']['cse_image'][0]['src'])) {
					$thumbnail = $item['pagemap']['cse_image'][0]['src'];
				}
				else {
					$thumbnail = $options['default_search_image_url'];
				}

				$content .= '<li class="ssgs_search_result_item">
		          <div class="ssgs_result_header">
			          <a href="' . $link . '"><img class="ssgs_result_thumbnail" alt="' . htmlentities($item['title']) .'" src="' . rawurldecode($thumbnail) . '" /></img></a>
			          <h3 class="ssgs_result_title"><a href="' . $link . '">' . $item['htmlTitle'] . '</a></h3>
		          </div>
		          <div class="ssgs_result_content">
			          <p class="ssgs_result_description">' .
			              $item['htmlFormattedUrl'] .
			              '<br />' .
			              $item['htmlSnippet'] .
			              '<a class="expand" href="' . $link . '">[' . __('more', 'ssgs') . ']</a>
					  </p>
		          </div>
	          </li>';
				}
			$content .= '</ul>';

			// Calculate new start value for "previous" link
			$previous = ($start > 1) ? ($start - $recordsPerPage) : null;
			$previous = (!is_null($previous) && ($previous < 1)) ? 1 : $previous;

			// Calculate new start value for "next" link
			$next = (($start + $recordsPerPage) <= $totalItems) ? ($start + $recordsPerPage) : null;

			// Display previous and next links if applicable
			if (!is_null($previous) || !is_null($next)) {
				$content .= '<ul class="pages">';
				if (!is_null($previous)) {
					$previous_link = $this->build_url($parsed_url, array(
						'totalItems' => $totalItems,
					    'start' => $previous));

					$content .= "<li><a href='$previous_link'>" . __("Previous", 'ssgs') . "</a></li>";
				}

				if (!is_null($next)) {
					$next_link = $this->build_url($parsed_url, array(
						'totalItems' => $totalItems,
						'start' => $next));

					$content .= "<li><a href='$next_link'>" . __("Next", 'ssgs') . "</a></li>";
				}

				$content .= "</ul>";
			}

        } // End else -- $totalItems <= 0
	} // End (!is_null($q))

	return $content;
    }

    function update($new_instance, $old_instance) {
        $instance = array();

        $instance['promote'] = ( ! empty( $new_instance['promote'] ) ) ? strip_tags( $new_instance['promote'] ) : 0;

        return $instance;

    }


    function form($instance) {
        $instance = wp_parse_args( $instance, array(
            'promote' => 0
	));
    }

	// http://php.net/manual/en/function.parse-url.php
	function build_url($parsed_url, $query_args=array()) {
		$parsed_url['query'] = http_build_query(array_merge($_GET, $query_args));

		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

		return "$scheme$user$pass$host$port$path$query$fragment";
	}
}

function ssgs_widget_init() {
	register_widget( 'SSGS_Widget' );
}

add_action( 'widgets_init', 'ssgs_widget_init' );
