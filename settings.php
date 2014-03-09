<?php

/*

Copyright 2014 Ole Jon Bjørkum

This file is part of SpotCommander.

SpotCommander is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SpotCommander is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SpotCommander.  If not, see <http://www.gnu.org/licenses/>.

*/

require_once('main.php');

$activity = array();
$activity['title'] = 'Settings';
$activity['actions'][] = array('action' => array('Apply', 'yes_32_img_div'), 'keys' => array('actions'), 'values' => array('apply_settings'));

?>

<div id="activity_inner_div" data-activitydata="<?php echo base64_encode(json_encode($activity)); ?>">

<div class="list_header_div"><div><div>UPDATES</div></div><div></div></div>

<div class="list_div">

<?php

$latest_version = (!empty($_COOKIE['latest_version']) && is_numeric($_COOKIE['latest_version'])) ? $_COOKIE['latest_version'] : 'unknown';

if(floatval($latest_version) > project_version)
{
	echo '
		<div class="setting_div actions_div" data-actions="open_external_activity" data-uri="' . project_website . '?download" onclick="void(0)">
		<div class="setting_text_div"><div><b>Update available</b></div><div>You are running version ' . number_format(project_version, 1) . '. The latest version is ' . $latest_version . '. Tap here to download the latest version.</div></div>
		</div>
	';
}
else
{
	echo '
		<div class="setting_div actions_div" data-actions="check_for_updates" onclick="void(0)">
		<div class="setting_text_div"><div>No updates available</div><div>You are running version ' . number_format(project_version, 1) . '. The latest version is ' . $latest_version . '. Tap here to check for updates now.</div></div>
		</div>
	';
}

?>

<div class="setting_div">
<div class="setting_text_div"><div>Check for updates</div><div>Automatically check for updates and notify if a newer version is available.</div></div>
<div class="setting_edit_div"><input type="checkbox" class="setting_checkbox" name="settings_check_for_updates"<?php echo setting_checkbox_status('settings_check_for_updates'); ?>></div>
</div>

</div>

<div id="android_app_settings_div">

<div class="list_header_div"><div><div>ANDROID APP SETTINGS</div></div><div></div></div>

<div class="list_div">

<div class="setting_div">
<div class="setting_text_div"><div>Keep screen on</div><div>Dim the screen instead of turning it off automatically. Will drain the battery faster.</div></div>
<div class="setting_edit_div"><input type="checkbox" class="setting_checkbox" name="settings_keep_screen_on"<?php echo setting_checkbox_status('settings_keep_screen_on'); ?>></div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Pause on incoming call</div><div>Pause the music when receiving a call.</div></div>
<div class="setting_edit_div"><input type="checkbox" class="setting_checkbox" name="settings_pause_on_incoming_call"<?php echo setting_checkbox_status('settings_pause_on_incoming_call'); ?>></div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Flip to pause</div><div>Pause the music when the device is flipped screen-down.</div></div>
<div class="setting_edit_div"><input type="checkbox" class="setting_checkbox" name="settings_flip_to_pause"<?php echo setting_checkbox_status('settings_flip_to_pause'); ?>></div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Notification</div><div>Show persistent notification with playback controls when in background. Android 4.1 and up.</div></div>
<div class="setting_edit_div"><input type="checkbox" class="setting_checkbox" name="settings_persistent_notification"<?php echo setting_checkbox_status('settings_persistent_notification'); ?>></div>
</div>

<div class="setting_div actions_div" data-actions="change_native_app_computer" onclick="void(0)">
<div class="setting_text_div"><div>Change computer</div><div>Tap here to change computer.</div></div>
</div>

</div>

</div>

<div class="list_header_div"><div><div>APP SETTINGS</div></div><div></div></div>

<div class="list_div">

<div class="setting_div">
<div class="setting_text_div"><div>Region</div><div>Set your region to hide unplayable tracks &amp; albums from search results.</div></div>
<div class="setting_edit_div">

<?php

$setting = 'settings_region';

$options = array(
	'ALL' => 'All',
	'AD' => 'Andorra',
	'AR' => 'Argentina',
	'AU' => 'Australia',
	'AT' => 'Austria',
	'BE' => 'Belgium',
	'BO' => 'Bolivia',
	'BG' => 'Bulgaria',
	'CL' => 'Chile',
	'CO' => 'Colombia',
	'CR' => 'Costa Rica',
	'CY' => 'Cyprus',
	'CZ' => 'Czech Republic',
	'DK' => 'Denmark',
	'DO' => 'Dominican Republic',
	'EC' => 'Ecuador',
	'SV' => 'El Salvador',
	'EE' => 'Estonia',
	'FI' => 'Finland',
	'FR' => 'France',
	'DE' => 'Germany',
	'GR' => 'Greece',
	'GT' => 'Guatemala',
	'HN' => 'Honduras',
	'HK' => 'Hong Kong',
	'HU' => 'Hungary',
	'IS' => 'Iceland',
	'IE' => 'Ireland',
	'IT' => 'Italy',
	'LV' => 'Latvia',
	'LI' => 'Liechtenstein',
	'LT' => 'Lithuania',
	'LU' => 'Luxembourg',
	'MY' => 'Malaysia',
	'MT' => 'Malta',
	'MX' => 'Mexico',
	'MC' => 'Monaco',
	'NL' => 'Netherlands',
	'NZ' => 'New Zealand',
	'NI' => 'Nicaragua',
	'NO' => 'Norway',
	'PA' => 'Panama',
	'PY' => 'Paraguay',
	'PE' => 'Peru',
	'PL' => 'Poland',
	'PT' => 'Portugal',
	'SG' => 'Singapore',
	'SK' => 'Slovakia',
	'ES' => 'Spain',
	'SE' => 'Sweden',
	'CH' => 'Switzerland',
	'TW' => 'Taiwan',
	'TR' => 'Turkey',
	'GB' => 'United Kingdom',
	'US' => 'United States',
	'UY' => 'Uruguay'
);

echo get_setting_dropdown($setting, $options);

?>

</div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Now playing refresh</div><div>How often to automatically refresh what's playing.</div></div>
<div class="setting_edit_div">

<?php

$setting = 'settings_nowplaying_refresh_interval';
$options = array(5 => '5 s', 10 => '10 s', 30 => '30 s', 60 => '60 s', 0 => 'Never');

echo get_setting_dropdown($setting, $options);

?>

</div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Start track radio simulation</div><div>Number of times to simulate the up keyboard key when starting a track radio. Depends on your Spotify client version.</div></div>
<div class="setting_edit_div">

<?php

$setting = 'settings_start_track_radio_simulation';
$options = array(5 => 5, 7 => 7, 8 => 8);

echo get_setting_dropdown($setting, $options);

?>

</div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Motion gestures</div><div>For example shake to play next track.</div></div>
<div class="setting_edit_div"><input type="checkbox" class="setting_checkbox" name="settings_motion_gestures"<?php echo setting_checkbox_status('settings_motion_gestures'); ?>></div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Motion sensitivity</div><div>Sensitivity for motion gestures.</div></div>
<div class="setting_edit_div">

<?php

$setting = 'settings_motion_sensitivity';
$options = array(15 => 'Higher', 20 => 'High', 30 => 'Normal', 40 => 'Low', 50 => 'Lower');

echo get_setting_dropdown($setting, $options);

?>

</div>
</div>

<div class="setting_div">
<div class="setting_text_div"><div>Keyboard shortcuts</div><div>Tap help below for a complete list of keyboard shortcuts.</div></div>
<div class="setting_edit_div"><input type="checkbox" class="setting_checkbox" name="settings_keyboard_shortcuts"<?php echo setting_checkbox_status('settings_keyboard_shortcuts'); ?>></div>
</div>

</div>

<div class="list_header_div"><div><div>ADVANCED</div></div><div></div></div>

<div class="list_div">

<div class="setting_div actions_div" data-actions="confirm_clear_cache" onclick="void(0)">
<div class="setting_text_div"><div>Clear cache</div><div>Tap here to clear the cache for playlists, albums, etc.</div></div>
</div>

<div class="setting_div actions_div" data-actions="confirm_restore_to_default" onclick="void(0)">
<div class="setting_text_div"><div>Restore</div><div>Tap here to restore settings, messages, warnings, etc.</div></div>
</div>

</div>

<div class="list_header_div"><div><div>WEB</div></div><div></div></div>

<div class="list_div">

<div class="setting_div actions_div" data-actions="open_external_activity" data-uri="<?php echo project_website; ?>?donate" onclick="void(0)">
<div class="setting_text_div"><div>Donate</div><div>Tap here to support the development <?php echo project_name; ?>.</div></div>
</div>

<div class="setting_div actions_div" data-actions="open_external_activity" data-uri="<?php echo project_website; ?>?use" onclick="void(0)">
<div class="setting_text_div"><div>Help</div><div>Tap here to get help.</div></div>
</div>

<div class="setting_div actions_div" data-actions="open_external_activity" data-uri="<?php echo project_website; ?>?issues" onclick="void(0)">
<div class="setting_text_div"><div>Report issue</div><div>Tap here to report an issue.</div></div>
</div>

<div class="setting_div actions_div" data-actions="open_external_activity" data-uri="<?php echo project_website; ?>" onclick="void(0)">
<div class="setting_text_div"><div>Visit website</div><div>Tap here to visit <?php echo project_name; ?>'s website.</div></div>
</div>

</div>

<div class="list_header_div"><div><div>ABOUT</div></div><div></div></div>

<div class="list_div">

<div class="setting_div actions_div" data-actions="open_external_activity" data-uri="<?php echo project_website; ?>?olejondotnet" onclick="void(0)">
<div class="setting_text_div"><div>By</div><div><?php echo project_developer; ?></div></div>
</div>

<div class="setting_div actions_div" data-actions="open_external_activity" data-uri="<?php echo project_website; ?>?license" onclick="void(0)">
<div class="setting_text_div"><div>License</div><div>GPLv3</div></div>
</div>

</div>

</div>
