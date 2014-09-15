<?php
/*
  Plugin Name: Event Espresso Template - Date Range Table
  Plugin URI: http://www.eventespresso.com
  Description: This template creates a list of events, displayed in a table - it filters events by a date range. [EVENT_CUSTOM_VIEW template_name="date-range"]
  Version: 1.0
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2013 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA

*/

//Shortcode Example: [EVENT_CUSTOM_VIEW template_name="date-range"]
//Requirements: CSS skills to customize styles, some renaming of the table columns

//Parameters
// user_select=true/false - default is True. If set to false the user will NOT be able to filter the results by date
// start_date="2013-10-01" - date is in international date format year-month-day so 1st October 1978  = 1978-10-01
// end_date="2013-10-01" - date is in international date format year-month-day so 1st October 1978  = 1978-10-01
// current_month="true" - It will display all events for the current month and current year.
// set_month="10" - numeric, the month of the year, e.g. January = 1, October = 10 -- Please note it will disregard year!!

// by setting the start date events with a start date before that will not appear at all.
// by setting an end date, events with a start date after that will not appear.

add_action('action_hook_espresso_custom_template_date-range','espresso_custom_template_date_range');

function espresso_custom_template_date_range(){

	global $this_event_id, $events, $ee_attributes;

	if(isset($ee_attributes['user_select'])) { $user_select = $ee_attributes['user_select']; }
	if(isset($ee_attributes['start_date'])) { $admin_start_date = strtotime($ee_attributes['start_date']); }
	if(isset($ee_attributes['end_date'])) { $admin_end_date = strtotime($ee_attributes['end_date']); }
	if(isset($ee_attributes['current_month'])) { $admin_currentmonth = $ee_attributes['current_month']; }
	if(isset($ee_attributes['set_month'])) { $admin_set_month = $ee_attributes['set_month']; }



	//Check for Multi Event Registration
	$multi_reg = false;
	if (function_exists('event_espresso_multi_reg_init')) {
		$multi_reg = true;
	}

	$cart_link 	= '';


	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );

	//var_dump($_POST);

	//Check for custom templates
	if(function_exists('espresso_custom_template_locate')) {
		$custom_template_path = espresso_custom_template_locate("date-range");
	} else {
		$custom_template_path = '';
	}

	if( !empty($custom_template_path) ) {
		//If custom template found include here
		include( $custom_template_path );
	} else {
		//Otherwise use the default template
		include( 'template.php' );
	}

}

/**
 * hook into PUE updates
 */
//Update notifications
add_action('action_hook_espresso_template_date_range_update_api', 'espresso_template_date_range_load_pue_update');
function espresso_template_date_range_load_pue_update() {
	global $org_options, $espresso_check_for_updates;
	if ( $espresso_check_for_updates == false )
		return;

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file
		require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
		$api_key = $org_options['site_license_key'];
		$host_server_url = 'http://eventespresso.com';
		$plugin_slug = array(
			'premium' => array('p'=> 'espresso-template-date-range'),
			'prerelease' => array('b'=> 'espresso-template-date-range-pr')
			);
		$options = array(
			'apikey' => $api_key,
			'lang_domain' => 'event_espresso',
			'checkPeriod' => '24',
			'option_key' => 'site_license_key',
			'options_page_slug' => 'event_espresso',
			'plugin_basename' => plugin_basename(__FILE__),
			'use_wp_update' => FALSE
		);
		$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
	}
}
