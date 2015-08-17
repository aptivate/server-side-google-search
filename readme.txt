=== Server-Side Google Search ===
Contributors: Aptivate
Tags: Server-Side Google Search, Google Search, Google Custom Search, Google, SCE, GCSE, Wordpress Google Search
Requires at least: 3.7
Tested up to: 4.3
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a lightweight Google Custom Search to your website, without the need for
JavaScript.

== Description ==

This plugin adds Google Custom Search to your website, but unlike other plugins
operates on the server side, thus eliminating the need for JavaScript and
keeping the page size small.

The admin interface is based on that used by the [WP Google Search plugin]
(https://wordpress.org/plugins/wp-google-search/)

The interface with the Google API is based on [Digital Collection Search](https://github.com/jasonclark/digital-collections-custom-search-api) by Jason Clark for Montana
State University.

= Available languages =
* English
* Spanish (incomplete)

[Follow this project on Github](https://github.com/aptivate/server-side-google-search)


== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory.
2. Activate it through the **Plugins** menu in WordPress.
3. Register your Google Custom Search Engine and get your Google Search Engine ID and API key here: https://www.google.com/cse/
4. Enable the plugin and enter the Google Search Engine ID and API key (**Settings** -> **Server-Side Google Search**)
5. If necessary, add the default search widget to the sidebar where you want to see it
6. Put the Server-Side Google Search widget on the sidebar where you want to see the results
7. Your theme will need to override the page that displays the "Nothing Found"
message when the search results are displayed.

= Example of how to add custom metadata to search results =

In your header.php:

`
<!--
<PageMap>
    <DataObject type="post_metadata">
        <Attribute name="modified_date" value="<?php the_modified_date( "M d, Y", '', '', true ); ?>" />
    </DataObject>
</PageMap>
-->
`

In your functions.php:


`
function add_modified_date( $metadata, $item_data ) {
	return $metadata . sprintf(
		'Last modified on: %s',
		$item_data['pagemap']['post_metadata'][0]['modified_date']
	);
}

add_filter( 'ssgs-add-post-search-metadata',
			'add_modified_date', 10, 2 );
`

== Changelog ==

= 1.0.3 =
* Removed deprecation warnings for WordPress 4.3
* Fixed potential bug where sort argument wasn't being preserved in links
* Updated test infrastructure to use wp-cli
* Documentation updates

= 1.0.2 =
* Added filter to allow custom metadata in search results

= 1.0.1 =
* Made display of URLs in search results optional (displayed by default)
* Right aligned sort options in search results

= 1.0.0 =
* First version

== Upgrade Notice ==

= 1.0.0 =
* First version


== Development ==

This plugin uses [wp-cli](http://wp-cli.org/) and [PHPUnit](https://phpunit.de/) for testing.
The tests require [runkit](https://github.com/zenovich/runkit) for mocking functions.

* Grab the latest source from github:

`
$ git clone git@github.com:aptivate/server-side-google-search.git
`

* Install [wp-cli](http://wp-cli.org/#install)
* Install [PHPUnit](https://phpunit.de/)
* Set up runkit:

`
$ git clone https://github.com/zenovich/runkit.git
$ cd runkit
$ phpize
$ ./configure
$ sudo make install
`

Add the following lines to `/etc/php5/cli/php.ini`:

`
extension=runkit.so
runkit.internal_override=1
`

* Install the test WordPress environment:

`
cd server-side-google-search
bash bin/install-wp-tests.sh test_db_name db_user 'db_password' db_host version
`

where:
** `test_db_name` is the name for your **temporary** test WordPress database
** `db_user` is the database user name
** `db_password` is the password
** `db_host` is the database host (eg `localhost`)
** `version` is the version of WordPress (eg `4.2.2` or `latest`)

* Run the tests
`phpunit`
