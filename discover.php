<?php

/*

Copyright 2013 Ole Jon Bjørkum

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
	$activity = array();
	$activity['title'] = 'Top lists';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="divider_div"><div><div>ALL</div></div><div></div></div>

		<div class="list_div">
	';

	$playlists = array(
		'Australia' => 'spotify:user:spotify:playlist:6lQMloCb0llJywSRoj3jAO',
		'Austria' => 'spotify:user:spotify:playlist:1f9qd5qJzIpYWoQm7Ue2uV',
		'Belgium' => 'spotify:user:spotify:playlist:13eazhZmMdf628WMqru34A',
		'Denmark' => 'spotify:user:spotify:playlist:2nQqWLiGEXLybDLu15ZmVx',
		'Finland' => 'spotify:user:spotify:playlist:6FZEbmeeb9aGiqSLAmLFJW',
		'France' => 'spotify:user:spotify:playlist:6FNC5Kuzhyt35pXtyqF6xq',
		'Germany' => 'spotify:user:spotify:playlist:4XEnSf75NmJPBX1lTmMiv0',
		'Netherlands' => 'spotify:user:spotify:playlist:7Jus9jsdpexXTXh2RVv8bZ',
		'New Zealand' => 'spotify:user:spotify:playlist:1TRzxr8LVu3OxdoMlabuNG',
		'Norway' => 'spotify:user:spotify:playlist:1BnqqOPMu8w08F1XpOzlwR',
		'Spain' => 'spotify:user:spotify:playlist:4z0aU3aX74LH6uWHTygTfV',
		'Sweden' => 'spotify:user:spotify:playlist:0Ks7MCeAZeYlBOmSLHmZ2o',
		'Switzerland' => 'spotify:user:spotify:playlist:1pDTi8rVKDQKGMb2NlJmDl',
		'United Kingdom' => 'spotify:user:spotify:playlist:7s8NU4MWP9GOSEXVwjcum4',
		'United States' => 'spotify:user:spotify:playlist:5nPXGgfCxfRpJHGRY4sovK',
		'Worldwide' => 'spotify:user:spotify:playlist:4hOKQuZbraPDIfaGbM3lKI',
	);

	$i = 0;

	foreach($playlists as $name => $uri)
	{
		$i++;

		$user = explode(':', $uri);
		$user = is_facebook_user(urldecode($user[2]));

		$actions_dialog = array();
		$actions_dialog['title'] = hsc($name);

		if(!playlist_is_saved($uri)) $actions_dialog['actions'][] = array('text' => 'Add to my playlists', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog add_playlist', $uri));

		$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
		$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));

		echo '
			<div class="list_item_div">
			<div title="' . hsc($name) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
			<div class="list_item_main_actions_arrow_div"></div>
			<div class="list_item_main_corner_arrow_div"></div>
			<div class="list_item_main_inner_div">
			<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div playlist_24_img_div"></div></div>
			<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . hsc($name) . '</div><div class="list_item_main_inner_text_lower_div">' . hsc($user) . '</div></div>
			</div>
			</div>
			<div class="list_item_actions_div">
			<div class="list_item_actions_inner_div">
			<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
			<div title="Play randomly" class="actions_div" data-actions="play_uri_randomly" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_uri_randomly_24_img_div"></div></div>
			<div title="Browse" class="actions_div" data-actions="change_activity" data-activity="playlists" data-subactivity="browse" data-args="uri=' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div browse_24_img_div"></div></div>
			<div title="Share" class="actions_div" data-actions="share_uri" data-uri="' . rawurlencode(uri_to_url($uri)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div share_24_img_div"></div></div>
			<div title="More" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
			</div>
			</div>
			</div>
		';			
	}

	echo '</div></div>';
}
elseif(isset($_GET['popular-playlists']))
{
	$activity = array();
	$activity['title'] = 'Popular playlists';

	$initial_results = 20;

	$files = get_external_files(array(project_website . 'popular-playlists.php'));
	$playlists = json_decode($files[0], true);

	if(!is_array($playlists))
	{
		$activity['actions'] = array('icon' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

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

			<div class="divider_div"><div><div>ALL</div></div><div></div></div>

			<div class="list_div">
		';

		$i = 0;

		foreach($playlists as $name => $uri)
		{
			$i++;

			$user = explode(':', $uri);
			$user = is_facebook_user(urldecode($user[2]));

			$actions_dialog = array();
			$actions_dialog['title'] = hsc($name);

			if(!playlist_is_saved($uri)) $actions_dialog['actions'][] = array('text' => 'Add to my playlists', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog add_playlist', $uri));

			$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));

			$class = ($i > $initial_results) ? 'hidden_div' : '';

			echo '
				<div class="list_item_div ' . $class . '">
				<div title="' . hsc($name) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
				<div class="list_item_main_actions_arrow_div"></div>
				<div class="list_item_main_corner_arrow_div"></div>
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div playlist_24_img_div"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . hsc($name) . '</div><div class="list_item_main_inner_text_lower_div">' . hsc($user) . '</div></div>
				</div>
				</div>
				<div class="list_item_actions_div">
				<div class="list_item_actions_inner_div">
				<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
				<div title="Play randomly" class="actions_div" data-actions="play_uri_randomly" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_uri_randomly_24_img_div"></div></div>
				<div title="Browse" class="actions_div" data-actions="change_activity" data-activity="playlists" data-subactivity="browse" data-args="uri=' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div browse_24_img_div"></div></div>
				<div title="Share" class="actions_div" data-actions="share_uri" data-uri="' . rawurlencode(uri_to_url($uri)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div share_24_img_div"></div></div>
				<div title="More" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}

		if(count($playlists) > $initial_results) echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all playlists</div></div></div>';

		echo '</div></div>';
	}
}
elseif(isset($_GET['most-streamed-shared']))
{
	$chart = $_GET['chart'];

	$countries = array(
		'AU' => 'Australia',
		'AT' => 'Austria',
		'BE' => 'Belgium',
		'DK' => 'Denmark',
		'EE' => 'Estonia',
		'FI' => 'Finland',
		'FR' => 'France',
		'DE' => 'Germany',
		'HK' => 'Hong Kong',
		'IS' => 'Iceland',
		'IE' => 'Ireland',
		'IT' => 'Italy',
		'LV' => 'Latvia',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MY' => 'Malaysia',
		'MX' => 'Mexico',
		'NL' => 'Netherlands',
		'NZ' => 'New Zealand',
		'NO' => 'Norway',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'SG' => 'Singapore',
		'ES' => 'Spain',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'GB' => 'United Kingdom',
		'US' => 'United States'
	);

	if(isset($_GET['country_code']))
	{
		$country_code = $_GET['country_code'];
		$country_name = $countries[$country_code];

		$tracks = get_chart($chart, $country_code);
		$count = count($tracks);

		$activity = array();
		$activity['title'] = $country_name;

		if(empty($tracks))
		{
			$activity['actions'] = array('icon' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get list. Try again.</div></div>

				</div>
			';
		}
		else
		{
			$queue = array();
			$i = 0;

			foreach($tracks as $track)
			{
				$queue[$i]['artist'] = $track['artist'];
				$queue[$i]['title'] = $track['title'];
				$queue[$i]['uri'] = $track['uri'];

				$i++;
			}

			$queue = base64_encode(json_encode($queue));

			$activity['actions'] = array('icon' => array('More', 'overflow_32_img_div'), 'keys' => array('actions'), 'values' => array('show_activity_overflow_actions'));
			$activity['overflow_actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $queue, 'false'));
			$activity['overflow_actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $queue, 'true'));

			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div class="divider_div"><div><div>ALL</div></div><div></div></div>

				<div class="list_div">
			';

			$initial_results = 20;
			$i = 0;

			foreach($tracks as $track)
			{
				$i++;

				$artist = $track['artist'];
				$title = $track['title'];
				$uri = $track['uri'];
				$plays = $track['plays'];

				$details_dialog = array();
				$details_dialog['title'] = hsc($title);
				$details_dialog['details'][] = array('detail' => 'Plays', 'value' => $plays);

				$actions_dialog = array();
				$actions_dialog['title'] = hsc($title);
				$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $uri));
				$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
				$actions_dialog['actions'][] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $uri));
				$actions_dialog['actions'][] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));

				if(!empty($plays)) $actions_dialog['actions'][] = array('text' => 'Details', 'keys' => array('actions', 'dialogdetails'), 'values' => array('hide_dialog show_details_dialog', base64_encode(json_encode($details_dialog))));

				$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($uri))));

				$class = ($i > $initial_results) ? 'hidden_div' : '';

				echo '
					<div class="list_item_div ' . $class . '">
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
					<div title="Star" class="actions_div" data-actions="star_uri" data-type="track" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . uri_is_starred($uri) . '_24_img_div"></div></div>
					<div title="More by ' . hsc($artist) . '" class="actions_div" data-actions="search_spotify" data-string="' . rawurlencode('artist:"' . $artist . '"') . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div search_24_img_div"></div></div>
					<div title="More" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
					</div>
					</div>
					</div>
				';
			}

			if($count > $initial_results) echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all tracks</div></div></div>';

			echo '</div></div>';
		}
	}
	else
	{
		$activity = array();
		$activity['title'] = 'Most streamed last week';

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="divider_div"><div><div>ALL</div></div><div></div></div>

			<div class="list_div">
		';

		foreach($countries as $country_code => $country_name)
		{
			echo '
				<div class="list_item_div">
				<div title="' . $country_name . '" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="most-streamed-shared" data-args="chart=' . $chart . '&amp;country_code=' . $country_code . '" onclick="void(0)">
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div playlist_24_img_div"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . $country_name . '</div></div>
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
	if(isset($_GET['letter']))
	{
		$letter = $_GET['letter'];

		$activity = array();
		$activity['title'] = 'Genres';

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="divider_div"><div><div>' . strtoupper($letter) . '</div></div><div></div></div>

			<div class="list_div">
		';

		$genres = get_db_rows('genres', "SELECT genre FROM genres ORDER BY genre", array('genre'));

		$initial_results = 20;

		$i = 0;

		foreach($genres as $genre)
		{
			$genre = $genre['genre'];

			if(string_starts_with($genre, strtoupper($letter)))
			{
				$i++;

				$class = ($i > $initial_results) ? 'hidden_div' : '';

				echo '
					<div class="list_item_div ' . $class . '">
					<div title="' . hsc($genre) . '" class="list_item_main_div actions_div" data-actions="search_spotify" data-string="' . rawurlencode('genre:"' . $genre . '"') . '" onclick="void(0)">
					<div class="list_item_main_inner_div">
					<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div genre_24_img_div"></div></div>
					<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . hsc($genre) . '</div></div>
					</div>
					</div>
					</div>
				';
			}		
		}

		if($i == 0)
		{
			echo '<div class="list_empty_div">No genres.</div>';
		}
		elseif($i > $initial_results)
		{
			echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_div" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all genres</div></div></div>';
		}

		echo '</div></div>';
	}
	else
	{
		$activity = array();
		$activity['title'] = 'Genres';

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="divider_div"><div><div>ALL</div></div><div></div></div>

			<div class="list_div">
		';

		$letters = range('a', 'z');

		$i = 0;

		foreach($letters as $letter)
		{
			$i++;

			echo '
				<div class="list_item_div">
				<div title="' . strtoupper($letter) . '" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="genres" data-args="letter=' . $letter . '" onclick="void(0)">
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div genres_24_img_div"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . strtoupper($letter) . '</div></div>
				</div>
				</div>
				</div>
			';
		}

		echo '</div></div>';
	}
}
else
{
	$activity = array();
	$activity['title'] = 'Discover';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="divider_div"><div><div>DISCOVER</div></div><div></div></div>

		<div class="list_div">

		<div class="list_item_div">
		<div title="Top lists" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="top-lists" data-args="" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div top_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Top lists</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="Most streamed last week" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="most-streamed-shared" data-args="chart=streamed" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div headphones_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Most streamed last week</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="Most shared last week" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="most-streamed-shared" data-args="chart=shared" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div share_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Most shared last week</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="Popular playlists" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="popular-playlists" data-args="" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div popular_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Popular playlists</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="New albums" class="list_item_main_div actions_div" data-actions="search_spotify" data-string="' . rawurlencode('tag:new') . '" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div album_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">New albums</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="Genres" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="genres" data-args="" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div genres_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Genres</div></div>
		</div>
		</div>
		</div>

		</div>

		<div class="divider_div"><div><div>INFORMATION</div></div><div></div></div>

		<ul>
		<li>Top lists are updated monthly</li>
		<li>Most streamed/shared may not work with old Spotify clients</li>
		<li>Popular playlists are updated weekly</li>
		<li>New albums are updated frequently</li>
		<li>All genres on Spotify are listed</li>
		<li>Some genres may not contain any tracks</li>
		</ul>

		</div>
	';
}

?>
