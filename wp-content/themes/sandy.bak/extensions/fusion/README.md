occupy-sandy-connector
======================

These modules provide an OccupySandy FusionTables to WordPress Connector designed to be dropped into your WordPress theme.

Install
-------
To install the module, first grab a copy of all the necessary modules. You can download them from the GitHub website, or you can install them using `git clone`:

	git clone https://github.com/radgeek/occupy-sandy-connector.git

You will need to drop copies of these files into your Theme directory. For example, if your theme is `occupysandy`, drop these files into: `wp-content/themes/occupysandy`.

After you've dropped these files into your Themes directory, you'll need to connect them to your theme. To do this, edit your Theme's `functions.php` file to add the following directives:

	global $os_regionToState;

	$os_regionToState = array(
	'rockaways' => 'NY',
	'lower east side' => 'NY',
	// Add any more regions that you may encounter here.
	// Enter the region name in all lowercase, and the state name as a two character postal code
	//
	// Example:
	// 	'hoboken' => 'nj',
	);

	require_once('occupysandybackend.php');
	require_once('occupysandyfrontend.php');

Now you'll need to connect your WordPress installation so that it can log in to your FusionTables database. Log in to your WordPress admin console and look for the new page under Settings -> FusionTables Backend. You'll need to add two settings: your API key and the ID for your table.
Paste these in to the text boxes and hit Save Changes. If all went well, instead of the error
message you should now see a table listing off the contents of your table beneath the settings page.

If all has gone well, you can now add a display of cards from your FusionTables backend to any WordPress template. Just use a template function call like this:

	<div class="container"> <!-- really this could be whatever container element you want -->
	<?php the_occupy_sandy_cards(); ?>
	</div>

To change how cards are displayed, edit the `card.php` template. Functions for accessing needed information are documented below.

Template Functions
------------------
The `card.php` template is called from a loop, triggered by `the_occupy_sandy_cards()`. From within that loop, you can access the current card using `get_the_occupy_sandy_card()`:

	<?php
	$card = get_the_occupy_sandy_card();

There are a number of template functions you can use to pull information for the card.

* `$card->get_card_class()`: returns an HTML class based on the type of location in the card. Uses **`hub`** for locations marked as Main Distribution Center, **`dropoff`** for locations marked as a drop-off location (only), **`volunteer`** for locations marked as volunteering locations (only), **`both`** for locations marked as both drop-off and volunteering locations, and **`unknown`** for locations marked with none of these types.

* `$card->get_card_heading()`: returns some automatically generated text to indicate the type of the location (drop-off, volunteer, both, distribution center, other...), to use in the heading bar on your card.

* `$card->is_drop_off()`: returns `TRUE` iff this record is marked as a drop-off location (among other things)

* `$card->is_volunteer()`: returns `TRUE` iff this record is marked as a volunteering location (among other things)

* `$card->is_distro_center()`: returns `TRUE` iff this record is marked as a Main Distribution Center

* `$card->is_other_type()`: returns `TRUE` iff this record is marked as neither a drop-off location, nor a volunteering location, nor a main distribution center

* `$card->get_state()`: attempts to get the two-letter postal code for the state of this location. If the State column is present in your database record, it uses that column. If it is not present, then it will attempt to guess from the value in the Region column. May return `NULL` if no state can be determined or guessed.

* `$card->get_title()`: returns a string containing the name of this location


* `$card->get_address()`: returns a string containing the street address of this location

* `$card->get_status()`: returns a string containing the Status text for this location, if a Status column exists in your database record. If no such column exists, returns `NULL`.

* `$card->get_times()`: returns a string containing times and dates for this location

* `$card->get_contact()`: returns a string containing the contact text for this location

* `$card->get_link()`: returns a string containing the Link HTML, if any, for this location

* `$card->get_description()`: returns a string containing the Description HTML, if any, for this location

* `$card->get_coordinates()`: returns an array containing the latitude and longitude for this location, for use with maps and other geodata consumers (example: `array("lat" => 40.712502, "long" => -73.935295)`)

* `$card->get_timestamp($fmt)`: returns a formatted date representing the last-updated timestamp for this record. The parameter `$fmt` accepts a [PHP date formatting string](http://us3.php.net/manual/en/function.date.php) (example: `$card->get_timestamp('M j, ga')` will return the date formated as `Nov 10, 4pm`; `$card->get_timestamp('U')` will return the date formatted as a Unix-epoch timestamp; etc.)

* `$card->has_field('Whatever')`: returns `TRUE` if this record has a column titled "Whatever." Returns `FALSE` otherwise.

* `$card->field('Whatever')`: returns the value of the `Whatever` column, if present. If there is no such column for this record, it returns `NULL`.

* `$card->columns()`: returns an array containing all the data columns available for this card

Data Access Functions
---------------------
If you need to access the data from the tables more directly, you can do so using the `get_occupy_sandy_data()` function.

	function get_occupy_sandy_data ($params = array())

The function returns a table containing all the records selected from your FusionTable. For example, if you call `$data = get_occupy_sandy_data(array("limit" => 1));` then data will contain an array of associative arrays, with each associative array representing one row from the FusionTable, and each element of the associative array representing one cell from that row -- something like this:

	array(1) {
	  [0] =>
	  array(16) {
	    'Timestamp' =>
	    string(18) "11/7/2012 13:30:00"
	    'hide_on_map' =>
	    string(0) ""
	    'Title' =>
	    string(25) "St Jacobi Lutheran Church"
	    'Address' =>
	    string(12) "5406 4th Ave"
	    'Description' =>
	    string(508) "<font color="red">URGENT: Update 11-07 11:54PM: Adult Diapers for 11/08</font> <br>REQUESTS: Cleaning supplies – Bleach, Ammonia, Mops, buckets, brooms dustpans, and Contractor Bags. Paper Towels & Shammies, Disinfectant wipes, First Aid stuff – neosporin, aspirin, tylenol, ibprofen and other OTC drugs, nasal spray, Batteries, Flashlights, Tarps, granny carts, shopping carts, wheelbarrows to transport goods to homes, Faces masks and respirators, Gas, & Gas Cans (ideally filled with gas). Generators."
	    'Link' =>
	    string(46) "http://interoccupy.net/occupysandy/sunsetpark/"
	    'Latitude' =>
	    double(40.644169)
	    'Longitude' =>
	    double(-74.015282)
	    'DateAndTimes' =>
	    string(0) ""
	    'IgnoreTimestamp' =>
	    string(0) ""
	    'Contact Info' =>
	    string(19) "Ronnie 646-353-5194"
	    'type' =>
	    string(24) "MAIN DISTRIBUTION CENTER"
	    'type marker' =>
	    string(1) "o"
	    'Show (0) / Hide (1)' =>
	    string(1) "0"
	    'Region' =>
	    string(11) "Sunset Park"
	    'Status' =>
	    string(0) ""
	  }
	}

You can alter what gets returned by sending different parameters in the `$params` argument:

	$data = get_occupy_sandy_data(array("limit" => 10, "offset" => 10));
	$data = get_occupy_sandy_data(array("where" => "Region='Sunset Park'"));
	$data = get_occupy_sandy_data(array("cols" => "Link"));

etc. Here are the parameters you can use:

* `cols`: Change the columns to return from the data table. `*` means all columns. Defaults to all columns if you don't specify specific columns to pull.

* `limit`: Set a maximum number of records to return. Defaults to returning all the records in the table if you don't specify a numeric limit.

* `offset`: Skip over this many records before beginning to collect records to return. (So, for example, `array('limit' => 10)` will return the first 10 records, beginning from the first record in the table; `array('limit' => 10, 'offset' => 10);` will return the next 10 records -- it will skip the first 10, and then return the next 10 after the offset).

* `table`: Specify another Fusion Table table ID to query, instead of the default ID you specified in your WordPress admin interface.

* `where`: Provide an SQL-style `WHERE` query to return a limited subset of the data records, filtered by their content.

* `raw`: Return the results in the raw format that Google's Fusion Table API returns them, rather than converting them into a set of associative arrays. (You'll need to use `var_dump()` to see the raw format that Google uses.)

* `fresh`: By default, requests to the data table are cached for 60 seconds. If `fresh` is set to `TRUE` it will force a fresh request to the Google API backend, rather than waiting for the cache to expire.



