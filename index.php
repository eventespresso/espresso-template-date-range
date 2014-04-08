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

?>
<?php
//var_dump($_POST);
?>

<div id="ee_date_range_wrapper">
	<?php
	if( !isset($ee_attributes['user_select']) || (isset($ee_attributes['user_select']) && $user_select != 'false')) { ?>
	<form action="" method="POST" id="ee_daterange_datepickers">
		<input class="datepicker small-text" id="ee_date_from" type="text" placeholder="<?php echo _e('Start Date','event_espresso'); ?>" name="ee_date_from">
		<input class="datepicker" id="ee_date_to" type="text" placeholder="<?php echo _e('End Date','event_espresso'); ?>" name="ee_date_to">
		<input id="ee_datesubmit" type="submit" name="datesubmit" value="<?php echo _e('Filter Events','event_espresso'); ?>">
	</form>
	<?php
	}
?>
	<table class="espresso-table" width="100%">
		<thead class="espresso-table-header-row">
			<tr>
				<th class="th-group"><?php _e('Course','event_espresso'); ?></th>
				<th class="th-group"><?php _e('Location','event_espresso'); ?></th>
				<th class="th-group"><?php _e('Date','event_espresso'); ?></th>
				<th class="th-group"><?php _e('','event_espresso'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php

      if(isset($_POST['ee_date_from'])) { strtotime($modified_date_from = $_POST['ee_date_from']); }
      if(isset($_POST['ee_date_to'])) { strtotime($modified_date_to = $_POST['ee_date_to']); }

      //$modified_date_from = strtotime($modified_date_from);
      //$modified_date_to = strtotime($modified_date_to);

		$thismonth = date('m');
		$thisyear = date('Y');

      foreach ($events as $event){

      	$event_start_date	= strtotime($event->start_date);
		$event_start_date_month = explode('-', $event->start_date);


      	//filter by current month
      	if(isset($admin_currentmonth) && $admin_currentmonth == "true") {
      		if($event_start_date_month['1'] == $thismonth && $event_start_date_month['0'] == (string)$thisyear) {  } else { continue; }
      	}

      	//filter by set month only if it is set and current month is not set to true
      	if(isset($admin_set_month) && !isset($admin_currentmonth)) {
      		if($event_start_date_month['1'] != $admin_set_month) { continue; }
      	}

      	if(!empty($admin_start_date) && $event_start_date < $admin_start_date) { continue; }
      	if(!empty($admin_end_date) && $event_start_date > $admin_end_date) { continue; }

      	if(isset($modified_date_to) != '' || isset($modified_date_from) != '') {
      	if($event_start_date < $modified_date_from || $event_start_date > $modified_date_to) { continue; }
		}

      	$button_text 		= __('Register', 'event_espresso');
		$alt_button_text	= __('View Details', 'event_espresso');//For alternate registration pages
		$externalURL 		= $event->externalURL;
		$button_text		= !empty($externalURL) ? $alt_button_text : $button_text;
		$registration_url 	= !empty($externalURL) ? $externalURL : espresso_reg_url($event->id);
		if ( ! has_filter( 'filter_hook_espresso_get_num_available_spaces' ) ){
			$open_spots		= apply_filters('filter_hook_espresso_get_num_available_spaces', $event->id); //Available in 3.1.37
		}else{
			$open_spots		= get_number_of_attendees_reg_limit($event->id, 'number_available_spaces');
		}
		$live_button = '<a id="a_register_link-'.$event->id.'" href="'.$registration_url.'">'.$button_text.'</a>';

		$event_status = event_espresso_get_status($event->id);

		if ($multi_reg && $event_status == 'ACTIVE') {
			$params = array(
				//REQUIRED, the id of the event that needs to be added to the cart
				'event_id' => $event->id,
				//REQUIRED, Anchor of the link, can use text or image
				'anchor' => __("Add to Cart", 'event_espresso'), //'anchor' => '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/cart_add.png" />',
				//REQUIRED, if not available at this point, use the next line before this array declaration
				// $event_name = get_event_field('event_name', EVENTS_DETAIL_TABLE, ' WHERE id = ' . $event_id);
				'event_name' => $event->event_name,
				//OPTIONAL, will place this term before the link
				'separator' => __(" or ", 'event_espresso')
			);

			$cart_link = event_espresso_cart_link($params);
		}


		if($open_spots < 1 && $event->allow_overflow == 'N') {
			$live_button = __('Sold Out', 'event_espresso');
			$cart_link = '';
		} else if ($open_spots < 1 && $event->allow_overflow == 'Y'){
			$live_button = !empty($event->overflow_event_id) ? '<a href="'.espresso_reg_url($event->overflow_event_id).'">'.__('Join Wait List', 'event_espresso').'</a>' : __('Sold Out', 'event_espresso');
			$cart_link = '';
		}

		if ($event_status == 'NOT_ACTIVE') {
			$live_button = __('Closed', 'event_espresso');
			$cart_link = '';
		}

	   ?>
			<tr class="espresso-table-row" value="<?php echo $event->start_date; ?>">
				<td class="td-group"><?php echo stripslashes_deep($event->event_name) ?></td>
				<td id="venue_title-<?php echo $event->id?>" class="venue_title"><?php echo $event->venue_name ?></td>
				<td class="td-group tddate"><?php echo event_date_display($event->start_date.' '.$event->start_time, get_option('date_format').' '.get_option('time_format')) ?></td>
				<td class="td-group"><?php echo event_espresso_get_status($event->id) == 'ACTIVE' ? $live_button .  $cart_link : $live_button; ?></td>
			</tr>
			<?php } //close foreach ?>
		</tbody>
	</table>
</div>
<style>

		#ee_date_from {
			width: 150px;
		}
		#ee_date_to {
			width: 150px;
		}
		#ee_datesubmit {
			float:right;
		}

</style>
<script>

jQuery(document).ready(function() {


		if( 0 < jQuery('.datepicker').length ) {
		    jQuery('#ee_date_from').datepicker({
		    	dateFormat: 'yy-mm-dd',
		    	altField: "#date_from_converted",
				altFormat: "yy-mm-dd",
				changeMonth: true,
     			changeYear: true
		    });
		    jQuery('#ee_date_to').datepicker({
		    	dateFormat: 'yy-mm-dd', //yy-mm-dd
		    	altField: "#date_to_converted",
				altFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true
		    });
		} // end if


jQuery('#ee_daterange_datepickers').append('<input type="hidden" id="date_from_converted">');
jQuery('#ee_daterange_datepickers').append('<input type="hidden" id="date_to_converted">');

jQuery("#ee_date_from").change(function(){
  if (!jQuery(this).val()) jQuery("#date_from_converted").val('');
});
jQuery("#ee_date_to").change(function(){
  if (!jQuery(this).val()) jQuery("#date_to_converted").val('');
});

		jQuery("#ee_datesubmit").click(function(e) {
			e.preventDefault();

			if(jQuery('#date_from_converted').val() != '') {
				add_time = jQuery('#date_from_converted').val();
				date_from = Date.parse(add_time);//, "Y-m-d");
			} 
			else 
			{date_from = '';}


			if(jQuery('#date_to_converted').val() != '') {
				add_time2 = jQuery('#date_to_converted').val();
				date_to = Date.parse(add_time2);//, "Y-m-d");
			} 
			else 
			{date_to = '';}

			if(date_from.length == 0 && date_to.length == 0) {
				jQuery('.espresso-table-row').each(function() {
					jQuery(this).show();
				});
			 }


			jQuery('.espresso-table-row').each(function() {
				
				row_date = Date.parse(jQuery(this).attr('value'));

				if(jQuery(date_from).length > 0 && jQuery(date_to).length == 0) {
					if(row_date <= date_from ) {
					jQuery(this).hide();
					}
					else { jQuery(this).show(); }
				}
				if(jQuery(date_to).length > 0 && jQuery(date_from).length == 0) {
					if(row_date >= date_to ) {
					jQuery(this).hide();
					}
					else { jQuery(this).show(); }
				}

				if(jQuery(date_to).length > 0 && jQuery(date_from).length > 0) {
					if(row_date < date_from || row_date > date_to ) {
					jQuery(this).hide();
					}
					else { jQuery(this).show(); }
				}


			});



		});

jQuery('tr.header').each(function(){
      if (jQuery(this).nextUntil("tbody").filter(function(){
           return jQuery(this).is(':visible');
      }).length == 0){
           jQuery('.espresso-table').apend('sfsdfsdfsdfsdf');
      } else {
           jQuery(this).show()
      }
});


});
</script>
<?php
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
