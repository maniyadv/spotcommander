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
$activity['title'] = 'Profile';

if(isset($_GET['is_authorized_with_spotify']))
{
	echo boolean_to_string(is_authorized_with_spotify);
}
else if(isset($_GET['deauthorize_from_spotify']))
{
	echo deauthorize_from_spotify();
}
elseif(isset($_GET['spotify_token']))
{
	store_spotify_token($_GET['spotify_token']);

	if(!is_authorized_with_spotify())
	{
		$activity['actions'][] = array('action' => array('Authorize with Spotify', 'key_32_img_div'), 'keys' => array('actions'), 'values' => array('confirm_authorize_with_spotify'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not authorize with Spotify. Tap the top right icon to try again.</div></div>

			</div>
		';	
	}
	else
	{
		$activity['actions'][] = array('action' => array('Profile', 'profile_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'profile', '', ''));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Success! You are now authorized with Spotify. Tap the top right icon to view your profile.</div></div>

			</div>
		';
	}
}
elseif(!is_authorized_with_spotify)
{
	$activity['actions'][] = array('action' => array('Authorize with Spotify', 'key_32_img_div'), 'keys' => array('actions'), 'values' => array('confirm_authorize_with_spotify'));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>You must authorize with Spotify. Tap the top right icon to continue.</div></div>

		</div>
	';
}
else
{
	$profile = get_profile();

	if(empty($profile))
	{
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get profile. Try again.</div></div>

			</div>
		';
	}
	else
	{
		$activity['actions'][] = array('action' => array('Deauthorize with Spotify', 'exit_32_img_div'), 'keys' => array('actions'), 'values' => array('confirm_deauthorize_from_spotify'));

		$style = (empty($profile['image'])) ? '' : 'background-size: cover; background-image: url(\'' . $profile['image'] . '\')';

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="profile_div">
			<div id="profile_image_div" style="' . $style . '"></div>
			<div id="profile_details_div">
			<div><b>Name:</b> ' . hsc($profile['name']) . '</div>
			<div><b>Username:</b> ' . $profile['username'] . '</div>
			<div><b>Country:</b> ' . $profile['country'] . '</div>
			<div><b>Subscription:</b> ' . ucfirst($profile['subscription']) . '</div>
			</div>
			</div>

			</div>
		';
	}
}

?>