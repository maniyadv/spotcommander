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

if(isset($_GET['search']))
{
	$string = rawurldecode($_GET['string']);
	$region = $_GET['region'];

	$total_results = 100;

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
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

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

		$initial_results = (is_array($tracks) && is_array($albums)) ? 5 : 20;
		$i = 0;

		if(is_array($tracks))
		{
			$actions_dialog = array();
			$actions_dialog['title'] = 'Sort by';
			$actions_dialog['actions'][] = array('text' => 'Popularity', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_tracks', 'default', 36500));
			$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_tracks', 'artist', 36500));
			$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_tracks', 'title', 36500));

			$list_header_title = (is_array($albums)) ? 'TRACKS' : 'ALL';

			echo '
				<div class="list_header_div"><div><div>' . $list_header_title . '</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_search_tracks') . '_24_img_div"></div></div></div>

				<div class="list_div">
			';

			$sort = $_COOKIE['settings_sort_search_tracks'];

			if($sort != 'default')
			{
				function tracks_cmp($a, $b)
				{
					global $sort;

					if($sort == 'artist')
					{
						return strcasecmp($a['artist'], $b['artist']);
					}
					elseif($sort == 'title')
					{
						return strcasecmp($a['title'], $b['title']);
					}
				}

				usort($tracks, 'tracks_cmp');
			}

			foreach($tracks as $track)
			{
				$artist = $track['artist'];
				$artist_uri = $track['artist_uri'];
				$title = $track['title'];
				$length = $track['length'];
				$popularity = $track['popularity'];
				$uri = $track['uri'];
				$album_uri = $track['album_uri'];
				$album_regions = $track['album_regions'];

				if(is_available_in_region($region, $album_regions) && $i < $total_results)
				{
					$i++;

					$details_dialog = array();
					$details_dialog['title'] = hsc($title);
					$details_dialog['details'][] = array('detail' => 'Length', 'value' => $length);
					$details_dialog['details'][] = array('detail' => 'Popularity', 'value' => $popularity);

					$actions_dialog = array();
					$actions_dialog['title'] = hsc($title);
					$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $album_uri));

					if(!empty($artist_uri)) $actions_dialog['actions'][] = array('text' => 'Browse artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_artist', $artist_uri));

					$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
					$actions_dialog['actions'][] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
					$actions_dialog['actions'][] = array('text' => 'Details', 'keys' => array('actions', 'dialogdetails'), 'values' => array('hide_dialog show_details_dialog', base64_encode(json_encode($details_dialog))));
					$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'title', 'uri'), 'values' => array('hide_dialog share_uri', hsc($title), rawurlencode(uri_to_url($uri))));

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
						<div title="More" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
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

		if(is_array($albums))
		{
			$default_sort = (get_search_type($string) == 'tag_new') ? 'Newest' : 'Popularity';

			$actions_dialog = array();
			$actions_dialog['title'] = 'Sort by';
			$actions_dialog['actions'][] = array('text' => $default_sort, 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_albums', 'default', 36500));
			$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_albums', 'artist', 36500));
			$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_albums', 'title', 36500));

			$list_header_title = (is_array($tracks)) ? 'ALBUMS' : 'ALL';

			echo '
				<div class="list_header_div"><div><div>' . $list_header_title . '</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_search_albums') . '_24_img_div"></div></div></div>

				<div class="list_div">
			';

			$sort = $_COOKIE['settings_sort_search_albums'];

			if($sort != 'default')
			{
				function albums_cmp($a, $b)
				{
					global $sort;

					if($sort == 'artist')
					{
						return strcasecmp($a['artist'], $b['artist']);
					}
					elseif($sort == 'title')
					{
						return strcasecmp($a['title'], $b['title']);
					}
				}

				usort($albums, 'albums_cmp');
			}

			foreach($albums as $album)
			{
				$artist = $album['artist'];
				$artist_uri = $album['artist_uri'];
				$title = $album['title'];
				$popularity = $album['popularity'];
				$uri = $album['uri'];
				$regions = $album['regions'];

				if(is_available_in_region($region, $regions) && $i < $total_results)
				{
					$i++;

					$details_dialog = array();
					$details_dialog['title'] = hsc($title);
					$details_dialog['details'][] = array('detail' => 'Popularity', 'value' => $popularity);

					$actions_dialog = array();
					$actions_dialog['title'] = hsc($title);
					$actions_dialog['actions'][] = array('text' => 'More by ' . hsc($artist), 'keys' => array('actions', 'string'), 'values' => array('hide_dialog search_spotify', rawurlencode('artist:"' . $artist . '"')));

					if(!empty($artist_uri)) $actions_dialog['actions'][] = array('text' => 'Browse artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_artist', $artist_uri));

					$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
					$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));
					$actions_dialog['actions'][] = array('text' => 'Details', 'keys' => array('actions', 'dialogdetails'), 'values' => array('hide_dialog show_details_dialog', base64_encode(json_encode($details_dialog))));
					$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'title', 'uri'), 'values' => array('hide_dialog share_uri', hsc($title), rawurlencode(uri_to_url($uri))));

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
						<div title="More" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
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
	$activity['actions'][] = array('action' => array('Clear', 'remove_32_img_div'), 'keys' => array('actions'), 'values' => array('clear_search_history'));

	$strings = get_db_rows('search-history', "SELECT string FROM search_history ORDER BY id DESC", array('string'));

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

			<div class="list_header_div"><div><div>ALL</div></div><div></div></div>

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
	$activity['actions'][] = array('action' => array('History', 'history_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'search', 'history', ''));

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
