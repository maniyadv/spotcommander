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

if(isset($_GET['search']))
{
	$string = rawurldecode($_GET['string']);
	$region = $_GET['region'];

	$total_results = 50;

	if(get_search_type($string) != 'tag_new') save_search_history($string);

	if(get_search_type($string) == 'track' || get_search_type($string) == 'isrc')
	{
		$search = search_spotify('tracks', $string);
	}
	elseif(get_search_type($string) == 'tag_new' || get_search_type($string) == 'upc')
	{
		$search = search_spotify('albums', $string);
	}
	else
	{
		$search = search_spotify('all', $string);
	}

	$activity = array();
	$activity['title'] = get_search_title($string);

	if(empty($search))
	{
		$activity['actions'] = array('icon' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Search API error. Try again.</div></div>

			</div>
		';
	}
	else
	{
		echo '<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">';

		$tracks = $search['tracks'];
		$albums = $search['albums'];

		$initial_results = (empty($tracks) || empty($albums)) ? 20 : 5;

		$i = 0;

		if(!empty($tracks))
		{
			$divider_title = (empty($albums)) ? 'ALL' : 'TRACKS';

			echo '
				<div class="divider_div">' . $divider_title . '</div>

				<div class="list_div">
			';

			$tracks = $tracks['tracks'];

			foreach($tracks as $track)
			{
				$artist = get_artists($track['artists']);
				$title = $track['name'];
				$uri = $track['href'];
				$album_uri = $track['album']['href'];
				$album_regions = $track['album']['availability']['territories'];

				if(is_available_in_region($region, $album_regions) && $i < $total_results)
				{
					$i++;

					$dialog_actions = array();
					$dialog_actions[] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $album_uri));
					$dialog_actions[] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
					$dialog_actions[] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $uri));
					$dialog_actions[] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
					$dialog_actions[] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($uri))));

					$class = ($i > $initial_results) ? 'hidden_div' : '';

					echo '
						<div class="list_item_div list_item_track_div ' . $class . '">
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
						<div title="More" class="actions_div" data-actions="show_dialog_actions" data-dialogactions="' . base64_encode(json_encode($dialog_actions)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
						</div>
						</div>
						</div>
					';
				}
			}

			if($i == 0)
			{
				echo '<div class="list_empty_div">No tracks.</div>';
			}
			elseif($i > $initial_results)
			{
				echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_track_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all tracks</div></div></div>';
			}

			echo '</div>';
		}

		$i = 0;

		if(!empty($albums))
		{
			$divider_title = (empty($tracks)) ? 'ALL' : 'ALBUMS';

			echo '
				<div class="divider_div">' . $divider_title . '</div>

				<div class="list_div">
			';

			$albums = $albums['albums'];

			foreach($albums as $album)
			{
				$artist = get_artists($album['artists']);
				$title = $album['name'];
				$uri = $album['href'];
				$regions = $album['availability']['territories'];

				if(is_available_in_region($region, $regions) && $i < $total_results)
				{
					$i++;

					$dialog_actions = array();
					$dialog_actions[] = array('text' => 'More by ' . hsc($artist), 'keys' => array('actions', 'string'), 'values' => array('hide_dialog search_spotify', rawurlencode('artist:"' . $artist . '"')));
					$dialog_actions[] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $uri));
					$dialog_actions[] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
					$dialog_actions[] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));
					$dialog_actions[] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($uri))));

					$class = ($i > $initial_results) ? 'hidden_div' : '';

					echo '
						<div class="list_item_div list_item_album_div ' . $class . '">
						<div title="' . hsc($artist . ' - ' . $title) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
						<div class="list_item_main_actions_arrow_div"></div>
						<div class="list_item_main_corner_arrow_div"></div>
						<div class="list_item_main_inner_div">
						<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div album_24_img_div"></div></div>
						<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . hsc($title) . '</div><div class="list_item_main_inner_text_lower_div">' . hsc($artist) . '</div></div>
						</div>
						</div>
						<div class="list_item_actions_div">
						<div class="list_item_actions_inner_div">
						<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
						<div title="Play randomly" class="actions_div" data-actions="play_uri_randomly" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_uri_randomly_24_img_div"></div></div>
						<div title="Browse" class="actions_div" data-actions="change_activity" data-activity="album" data-subactivity="" data-args="uri=' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div browse_24_img_div"></div></div>
						<div title="Star" class="actions_div" data-actions="star_uri" data-type="album" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . uri_is_starred($uri) . '_24_img_div"></div></div>
						<div title="More" class="actions_div" data-actions="show_dialog_actions" data-dialogactions="' . base64_encode(json_encode($dialog_actions)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
						</div>
						</div>
						</div>
					';
				}
			}

			if($i == 0)
			{
				echo '<div class="list_empty_div">No albums.</div>';
			}
			elseif($i > $initial_results)
			{
				echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_album_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all albums</div></div></div>';
			}

			echo '</div>';
		}

		echo '</div>';
	}
}
elseif(isset($_GET['clear']))
{
	echo clear_search_history();
}
elseif(isset($_GET['history']))
{
	$activity = array();
	$activity['title'] = 'Search history';
	$activity['actions'] = array('icon' => array('Clear', 'remove_32_img_div'), 'keys' => array('actions'), 'values' => array('clear_search_history'));

	$strings = get_db_rows('search-history', "SELECT * FROM search_history ORDER BY id DESC", array('string'));

	if(empty($strings))
	{
		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Empty search history</div></div>

			</div>
		';
	}
	else
	{
		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="divider_div">ALL</div>

			<div class="list_div">
		';

		foreach($strings as $string)
		{
			$string = $string['string'];
			$title = get_search_title($string);

			echo '
				<div class="list_item_div">
				<div title="' . hsc($title) . '" class="list_item_main_div actions_div" data-actions="search_spotify" data-string="' . rawurlencode($string) . '" onclick="void(0)">
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div search_24_img_div"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . hsc($title) . '</div></div>
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
	$activity['title'] = 'Search';
	$activity['actions'] = array('icon' => array('History', 'history_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'search', 'history', ''));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_form_div">
		<form method="post" action="." id="search_form" autocomplete="off" autocapitalize="off">
		<div class="input_text_div"><div class="input_text_bottom_border_div"></div><div class="input_text_left_border_div"></div><input type="text" id="search_input" value="Search..." data-hint="Search..."><div class="input_text_right_border_div"></div></div>
		<div class="invisible_div"><input type="submit" value="Search"></div>
		</form>
		</div>

		</div>
	';
}

?>
