<?php
class SSGS_Widget extends WP_Widget {
	private $options;

	public function __construct() {
		parent::WP_Widget( false, $name = __( 'Server-Side Google Search (SSGS)','ssgs' ) );
	}

	public function widget( $args, $instance ) {
		$this->options = get_option( 'ssgs_general_settings' );

		global $ssgs;

		echo $args['before_widget'];

		$content  = '<div class="ssgs-result-wrapper">';
		$q = $this->get_search_string();
		if ( ! is_null( $q ) ) {
			$content .= $this->get_search_results();
		}
		$content .= '</div>';

		echo apply_filters( 'ssgs_widget_content', $content );

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
		$response = $this->get_api_response();
		if ( $response === false ) {
			// API call failed, display message to user
			return '<p><strong>' . __( 'An error was encountered while performing the requested search', 'ssgs' ) . '</strong></p>'."\n";
		}

		// Decode json object(s) out of response from Google Ajax Search API
		$result = json_decode( $response, true );

		$total_items = $this->get_total_items( $result );
		if ( $total_items <= 0 ) {
			$content = '<p><strong>' . __( 'Sorry, there were no results', 'ssgs' ) ."</strong></p>\n";
		}else {
			$content = $this->get_results_content( $result );

		} // End else -- $total_items <= 0

		return $content;
	}

	private function get_results_content( $result ) {
		// Make sure some results were returned, show results as html with result numbering and pagination
		$content = $this->get_results_header( $result );
		$content .=	'<div class="ssgs-result-facet">';

		$sort = $this->get_sort();
		$content .= $this->get_facet_filter( $sort );
		$content .= $this->get_result_list( $result['items'] );
		$content .= $this->get_other_pages_content( $result );

		$content .= '</div>';

		return $content;
	}

	private function get_other_pages_content( $result ) {
		$page_length = $this->get_page_length();
		$start = $this->get_page_start();

		$total_items = $this->get_total_items( $result );

		// Calculate new start value for "previous" link
		$previous = $start - $page_length;
		if ( $previous < 1 ) {
			$previous = false;
		}

		// Calculate new start value for "next" link
		$next = $start + $page_length;
		if ( $next > $total_items ) {
			$next = false;
		}

		// Display previous and next links if applicable
		if ( $previous || $next ) {
			$content = '<div class="ssgs-pages">';
			$content .= $this->get_previous_link( $previous, $total_items );

			$content .= '<ul class="ssgs-numbers">' .
				$this->get_pages( $start, $total_items, $page_length ) .
				'</ul>';

			$content .= $this->get_next_link( $next, $total_items );

			$content .= '</div>';
		}

		return $content;
	}

	private function get_previous_link( $previous, $total_items ) {
		$content = '';

		if ( $previous ) {
			$previous_link = $this->build_href( array(
				'totalItems' => $total_items,
				'start' => $previous,
			) );

			$content .= "<a class='ssgs-prev' href='$previous_link'>&laquo;</a>";
		}

		return $content;
	}

	private function get_next_link( $next, $total_items ) {
		$content = '';

		if ( $next ) {
			$next_link = $this->build_href(array(
				'totalItems' => $total_items,
				'start' => $next,
			) );

			$content .= "<a class='ssgs-next' href='$next_link'>&raquo;</a>";
		}

		return $content;
	}

	private function get_api_response() {
		// Build request and send to Google Ajax Search API
		if ( $this->options['results_source'] == 'test' ) {
			return $this->get_mock_response();
		}

		return $this->get_google_response();
	}

	private function get_results_header( $result ) {
		$total_items = $this->get_total_items( $result );
		$q = $this->get_search_string();
		$results_displayed = count( $result['items'] );

		$content = '<h2 class="ssgs-result-page-title">' . __( 'Search for', 'ssgs' ).
			' <strong>' . stripslashes( urldecode( $q ) ) . '</strong></h2>' .
			'<div class="ssgs-results-info">' .
			sprintf( __( 'Displaying %d items from around %d matches', 'ssgs' ),
					 $results_displayed, $total_items) . '</div>';

		return $content;
	}

	private function get_facet_filter( $sort ) {
		$relevance_url = $this->build_href( array( 'sort' => '' ) );
		$date_url = $this->build_href( array( 'sort' => 'date' ) );

		$date_classes = array( 'ssgs-results-sort-date' );
		$relevance_classes = array( 'ssgs-results-sort-relevance' );

		if ( $sort == 'date' ) {
			$date_classes[] = 'selected';
		} else {
			$relevance_classes[] = 'selected';
		}

		$date_classes = implode( ' ', $date_classes );
		$relevance_classes = implode( ' ', $relevance_classes );

		$content = '<ul class="ssgs-facet-filter">' .
			"<span class='ssgs-facet-heading'>" . __( 'Sort', 'ssgs' ) . "</span>
				<li class='$relevance_classes'><a class='ssgs-facet-link' href='$relevance_url'>" . __( 'Relevance', 'ssgs' ). "</a></li>
				<li class='$date_classes'><a class='ssgs-facet-link' href='$date_url'>" . __( 'Date', 'ssgs' ) . "</a></li>
				</ul>";

		return $content;
	}

	private function get_result_list( $items ) {
		$content .= '<ul class="ssgs-result-list">';

		foreach ( $items as $item ) {
			$link = rawurldecode( $item['link'] );

			$thumbnail = $this->get_thumbnail( $item['pagemap'] );

			if ( $item['metatags-modified-date'] ) {
				$date = "<span class='ssgs-modified-date'>{$item['metatags-modified-date']}</span> - ";
			} else {
				$date = '';
			}

			$content .= '<li class="ssgs-search-result-item">
				  <div class="ssgs-result-header">
					  <a href="' . $link . '"><img class="ssgs-result-thumbnail" alt="' . htmlentities( $item['title'] ) .'" src="' . rawurldecode( $thumbnail ) . '" /></a>
					  <h3 class="ssgs-result-title"><a href="' . $link . '">' . $item['htmlTitle'] . '</a></h3>
				  </div>
				  <div class="ssgs-result-content">
					  <div class="ssgs-result-description">' .
			"<p class='ssgs-html-formatted-url'>{$item['htmlFormattedUrl']}</p>" .
				"<p class='ssgs-snippet'>$date<span class='ssgs-html-snippet'>{$item['htmlSnippet']}</span>" .
				'<a class="ssgs-expand" href="' . $link . '">[' . __( 'more', 'ssgs' ) . ']</a>
						   </p>
					  </div>
				  </div>
			  </li>';
		}
		$content .= '</ul>';

		return $content;
	}

	private function get_thumbnail( $pagemap ) {
		if ( isset( $pagemap['metatags'][0]['thumbnailurl'] ) ) {
			return $pagemap['metatags'][0]['thumbnailurl'];
		}

		if ( isset( $pagemap['cse_thumbnail'][0]['src'] ) ) {
			return $pagemap['cse_thumbnail'][0]['src'];
		}

		if ( isset( $pagemap['cse_image'][0]['src'] ) ) {
			return $pagemap['cse_image'][0]['src'];
		}

		return $this->options['default_search_image_url'];
	}

	private function get_total_items( $result ) {
		$total_items = isset( $_GET['totalItems'] ) ?  strip_tags( (int)$_GET['totalItems'] ) : $result['queries']['request'][0]['totalResults'];

		// The free version of Google Custom Search only allows 100 results to be returned
		if ( $this->options['edition'] == 'free' && $total_items > 100 ) {
			$total_items = 100;
		}

		return $total_items;
	}

	private function get_google_response() {
		$api_key = $this->options['google_search_api_key'];
		$id = $this->options['google_search_engine_id'];
		$page_length = $this->get_page_length();
		$q = $this->get_search_string();
		$sort = $this->get_sort();
		$api_version = 'v1';
		$start = $this->get_page_start();

		// Set URL for the Google Custom Search API call
		$url = array(
			'scheme' => 'https',
			'host' => 'www.googleapis.com',
			'path' => "/customsearch/$api_version",
		);

		$query_args = array(
			'key' => $api_key,
			'cx' => $id,
			'alt' => 'json',
			'num' => $page_length,
			'start' => $start,
			'prettyprint' => 'true',
			'q' => $q,
		);

		if ( $sort ) {
			$query_args['sort'] = $sort;
		}

		return file_get_contents( $this->build_url( $url, $query_args ) );
	}

	private function get_page_length() {
		// Number of records to display per page (1 - 10)
		$items_per_page = 10;

		// Set default value for page length (number of entries to display)
		$page_length = isset( $_GET['limit'] ) ? strip_tags( (int)$_GET['limit'] ) : $items_per_page;

		return $page_length;
	}

	private function get_page_start() {
		// Set default value for page start index
		$start = isset( $_GET['start'] ) ? strip_tags( (int)$_GET['start'] ) : '1';

		return $start;
	}

	private function get_search_string() {
		$q = isset( $_GET['s'] ) ? urlencode( strip_tags( trim( $_GET['s'] ) ) ) : null;

		return $q;
	}

	private function get_sort() {
		// Set default value for results sorting
		$sort = isset( $_GET['sort'] ) ? htmlentities( strip_tags( $_GET['sort'] ) ) : false;

		return $sort;
	}

	private function get_pages( $current_start, $total_items, $items_per_page ) {
		$pages = '';

		$current_page = (int)($current_start / $items_per_page) + 1;
		$total_pages = ceil( $total_items / $items_per_page );
		$page_numbers = $this->get_page_numbers( $current_page, $total_pages );

		foreach ( $page_numbers as $page_number ) {
			if ( $page_number == $current_page ) {
				$page_link = "<span class='ssgs-page'>$page_number</span>";
			} else {
				$start = ($page_number - 1) * $items_per_page + 1;

				$page_link = $this->get_page_link(
					$page_number, $start, $total_items);
			}

			$pages .= "<li>$page_link</li>";
		}

		return $pages;
	}

	private function get_page_numbers( $current_page, $total_pages ) {
		$first_page = $current_page - 5;
		if ( $first_page < 1 ) {
			$first_page = 1;
		}

		$last_page = $first_page + 9;
		if ( $last_page > $total_pages ) {
			$last_page = $total_pages;
		}

		return range( $first_page, $last_page );
	}

	private function get_page_link( $page_index, $start, $total_items ) {
		$href = $this->build_href(array(
			'totalItems' => $total_items,
			'start' => $start,
		));

		return "<a class='ssgs-page' href='$href'>$page_index</a>";
	}

	private function build_href( $query_args = array() ) {
		global $wp;

		$parsed_url = parse_url( home_url( add_query_arg( array(), $wp->request ) ) );
		$query_args = array_merge( $_GET, $query_args );

		return htmlentities( $this->build_url( $parsed_url, $query_args ) );
	}

	// http://php.net/manual/en/function.parse-url.php
	private function build_url( $parsed_url, $query_args = array() ) {
		$parsed_url['query'] = http_build_query( $query_args );

		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host	 = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port	 = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user	 = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass	 = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass	 = ($user || $pass) ? "$pass@" : '';
		$path	 = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query	= isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

		return "$scheme$user$pass$host$port$path$query$fragment";
	}

	private function get_mock_response() {
		return file_get_contents( dirname( __FILE__ ) . '/mock_results.json' );
	}
}

function ssgs_widget_init() {
	register_widget( 'SSGS_Widget' );
}

add_action( 'widgets_init', 'ssgs_widget_init' );

if ( ! function_exists( 'debugger' ) ) {
	function debugger() {
		// hook for geben on emacs
	}
}
