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

if(isset($_GET['top-lists']))
{
	$country = get_spotify_country();
	$country = (empty($country)) ? 'US' : $country;

	$activity = array();
	$activity['title'] = 'Top lists in ' . get_country_name($country);

	$files = get_external_files(array(project_website . 'api/1/discover/top-lists/?country=' . $country), null, null);
	$playlists = json_decode($files[0], true);

	if(!is_array($playlists))
	{
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get playlists. Try again.</div></div>

			</div>
		';
	}
	else
	{
		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="cards_vertical_div">
		';

		foreach($playlists as $playlist)
		{
			$name = $playlist['name'];
			$followers = number_format($playlist['followers'], 0, '.', ',');
			$uri = $playlist['uri'];
			$cover_art = $playlist['cover_art'];

			echo '<div title="' . hsc($name) . '" class="card_vertical_div actions_div" data-actions="browse_playlist" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($name) . '</div><div class="card_vertical_lower_div">Followers: ' . $followers . '</div></div>';
		}

		echo '<div class="clear_float_div"></div></div></div>';
	}
}
elseif(isset($_GET['popular-playlists']))
{
	$activity = array();
	$activity['title'] = 'Popular playlists';

	$files = get_external_files(array(project_website . 'api/2/discover/popular-playlists/'), null, null);
	$playlists = json_decode($files[0], true);

	if(!is_array($playlists))
	{
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get playlists. Try again.</div></div>

			</div>
		';
	}
	else
	{
		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="cards_vertical_div">
		';

		foreach($playlists as $playlist)
		{
			$name = $playlist['name'];
			$uri = $playlist['uri'];
			$cover_art = $playlist['cover_art'];

			$user = explode(':', $uri);
			$user = is_facebook_user(urldecode($user[2]));

			echo '<div title="' . hsc($name) . '" class="card_vertical_div actions_div" data-actions="browse_playlist" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . is_authorized_with_spotify . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($name) . '</div><div class="card_vertical_lower_div">' . $user . '</div></div>';
		}

		echo '<div class="clear_float_div"></div></div></div>';
	}
}
elseif(isset($_GET['new-releases']))
{
	$country = get_spotify_country();
	$country = (empty($country)) ? 'US' : $country;

	$activity = array();
	$activity['title'] = 'New releases in ' . get_country_name($country);

	$files = get_external_files(array(project_website . 'api/1/discover/new-releases/?country=' . $country), null, null);
	$albums = json_decode($files[0], true);

	if(!is_array($albums))
	{
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get releases. Try again.</div></div>

			</div>
		';
	}
	else
	{
		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="cards_vertical_div">
		';

		foreach($albums as $album)
		{
			$artist = $album['artist'];
			$title = $album['title'];
			$released = $album['released'];
			$uri = $album['uri'];
			$cover_art = $album['cover_art'];

			echo '<div title="' . hsc($artist . ' - ' . $title) . ' (' . $released . ')" class="card_vertical_div actions_div" data-actions="browse_album" data-uri="' . $uri . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($title) . '</div><div class="card_vertical_lower_div">' . hsc($artist) . '</div></div>';
		}

		echo '<div class="clear_float_div"></div></div></div>';
	}
}
elseif(isset($_GET['most-streamed-shared']))
{
	$chart = $_GET['chart'];

	$metadata = get_chart($chart);

	$activity = array();
	$activity['title'] = 'Most ' . $chart . ' in ' . $metadata['country'];

	if(empty($metadata))
	{
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get list. Try again.</div></div>

			</div>
		';
	}
	else
	{
		$tracks = $metadata['tracks'];

		$add_to_playlist_uris = '';
		$queue = array();
		$i = 0;

		foreach($tracks as $track)
		{
			$add_to_playlist_uris .= $track['uri'] . ' ';

			$queue[$i]['artist'] = $track['artist'];
			$queue[$i]['title'] = $track['title'];
			$queue[$i]['uri'] = $track['uri'];

			$i++;
		}

		$add_to_playlist_uris = trim($add_to_playlist_uris);

		$queue = base64_encode(json_encode($queue));

		$activity['actions'][] = array('action' => array('Add to playlist', ''), 'keys' => array('actions', 'title', 'uri', 'isauthorizedwithspotify'), 'values' => array('hide_dialog add_to_playlist', $activity['title'], $add_to_playlist_uris, is_authorized_with_spotify));
		$activity['actions'][] = array('action' => array('Queue tracks', ''), 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $queue, 'false'));
		$activity['actions'][] = array('action' => array('Queue tracks randomly', ''), 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $queue, 'true'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="list_header_div"><div><div>ALL</div></div><div></div></div>

			<div class="list_div">
		';

		foreach($tracks as $track)
		{
			$artist = $track['artist'];
			$title = $track['title'];
			$uri = $track['uri'];
			$plays = $track['plays'];

			$details_dialog = array();
			$details_dialog['title'] = hsc($title);
			$details_dialog['details'][] = array('detail' => 'Plays', 'value' => $plays);

			$actions_dialog = array();
			$actions_dialog['title'] = hsc($title);
			$actions_dialog['actions'][] = array('text' => 'Add to playlist', 'keys' => array('actions', 'title', 'uri', 'isauthorizedwithspotify'), 'values' => array('hide_dialog add_to_playlist', $title, $uri, is_authorized_with_spotify));
			$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $uri));
			$actions_dialog['actions'][] = array('text' => 'Search artist', 'keys' => array('actions', 'string'), 'values' => array('hide_dialog get_search', rawurlencode('artist:"' . $artist . '"')));
			$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
			$actions_dialog['actions'][] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));

			if(!empty($plays)) $actions_dialog['actions'][] = array('text' => 'Details', 'keys' => array('actions', 'dialogdetails'), 'values' => array('hide_dialog show_details_dialog', base64_encode(json_encode($details_dialog))));

			$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'title', 'uri'), 'values' => array('hide_dialog share_uri', hsc($title), rawurlencode(uri_to_url($uri))));

			echo '
				<div class="list_item_div">
				<div title="' . hsc($artist . ' - ' . $title) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-trackuri="' . $uri . '" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
				<div class="list_item_main_actions_arrow_div"></div>
				<div class="list_item_main_corner_arrow_div"></div>
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div ' . track_is_playing($uri, 'icon') . '"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div ' . track_is_playing($uri, 'text') . '">' . hsc($title) . '</div><div class="list_item_main_inner_text_lower_div">' . hsc($artist) . '</div></div>
				</div>
				</div>
				<div class="list_item_actions_div">
				<div class="list_item_actions_inner_div">
				<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
				<div title="Queue" class="actions_div" data-actions="queue_uri" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div queue_24_img_div"></div></div>
				<div title="Save to/remove from library" class="actions_div" data-actions="save" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_saved($uri) . '_24_img_div"></div></div>
				<div title="Browse artist" class="actions_div" data-actions="browse_artist" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div artist_24_img_div"></div></div>
				<div title="More" class="show_actions_dialog_div actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}

		echo '</div></div>';
	}
}
elseif(isset($_GET['genres']))
{
	$country = get_spotify_country();
	$country = (empty($country)) ? 'US' : $country;

	if(isset($_GET['name']) && isset($_GET['genre']))
	{
		$activity = array();
		$activity['title'] = rawurldecode($_GET['name']);

		$files = get_external_files(array(project_website . 'api/1/discover/genre/?genre=' . $_GET['genre'] . '&country=' . $country), null, null);
		$playlists = json_decode($files[0], true);

		if(!is_array($playlists))
		{
			$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get genre. Try again.</div></div>

				</div>
			';
		}
		else
		{
			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div class="cards_vertical_div">
			';

			foreach($playlists as $playlist)
			{
				$name = $playlist['name'];
				$description = $playlist['description'];
				$followers = number_format($playlist['followers'], 0, '.', ',');
				$uri = $playlist['uri'];
				$cover_art = $playlist['cover_art'];

				echo '<div title="' . hsc($name . ': ' . $description) . '" class="card_vertical_div actions_div" data-actions="browse_playlist" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($name) . '</div><div class="card_vertical_lower_div">Followers: ' . $followers . '</div></div>';
			}

			echo '<div class="clear_float_div"></div></div></div>';
		}
	}
	else
	{
		$activity = array();
		$activity['title'] = 'Genres';

		$files = get_external_files(array(project_website . 'api/1/discover/genres/?country=' . $country), null, null);
		$genres = json_decode($files[0], true);

		if(!is_array($genres))
		{
			$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get genres. Try again.</div></div>

				</div>
			';
		}
		else
		{
			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div class="cards_vertical_div">
			';

			foreach($genres as $genre)
			{
				$name = $genre['name'];
				$space = $genre['space'];
				$cover_art = $genre['cover_art'];

				echo '<div title="' . hsc($name) . '" class="card_vertical_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="genres" data-args="name=' . rawurlencode($name) . '&genre=' . $space . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($name) . '</div><div class="card_vertical_lower_div">Genre</div></div>';
			}

			echo '<div class="clear_float_div"></div></div></div>';
		}
	}
}
else
{
	$country = get_spotify_country();
	$country = (empty($country)) ? 'US' : $country;
	$country = get_country_name($country);

	$activity = array();
	$activity['title'] = 'Discover';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="discover_div" class="cards_div">
		<div>
		<div>
		<div class="card_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="top-lists" data-args="" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div top_24_img_div"></div></div><div class="card_text_div"><div>Top lists</div><div>In ' . $country . '.</div></div></div>
		<div class="card_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="popular-playlists" data-args="" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div popular_24_img_div"></div></div><div class="card_text_div"><div>Popular playlists</div><div>Updated weekly.</div></div></div>
		<div class="card_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="genres" data-args="" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div genres_24_img_div"></div></div><div class="card_text_div"><div>Genres &amp; moods</div><div>Playlists based on genres and moods.</div></div></div>		</div>
		<div>
		<div class="card_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="new-releases" data-args="" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div album_24_img_div"></div></div><div class="card_text_div"><div>New releases</div><div>In ' . $country . '.</div></div></div>
		<div class="card_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="most-streamed-shared" data-args="chart=streamed" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div headphones_24_img_div"></div></div><div class="card_text_div"><div>Most streamed</div><div>Last week in ' . $country . '.</div></div></div>
		<div class="card_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="most-streamed-shared" data-args="chart=shared" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div share_24_img_div"></div></div><div class="card_text_div"><div>Most shared</div><div>Last week in ' . $country . '.</div></div></div>
		</div>
		</div>
		</div>

		</div>
	';
}

?>
