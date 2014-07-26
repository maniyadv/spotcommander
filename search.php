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

if(isset($_GET['clear']))
{
	echo clear_search_history();
}
elseif(isset($_GET['search']))
{
	$string = rawurldecode($_GET['string']);

	if(preg_match('/^(.+?):"(.+?)"$/', $string, $matches)) $string = $matches[1] . ':"' . str_replace('"', '', $matches[2]) . '"';
	if(preg_match('/^artist:"(.+?), .+"$/', $string, $matches)) $string = 'artist:"' . $matches[1] . '"';

	save_search_history($string);

	$search = get_search($string);

	$activity = array();
	$activity['title'] = get_search_title($string);

	if(empty($search))
	{
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get search results. Try again.</div></div>

			</div>
		';
	}
	else
	{
		echo '<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">';

		$tracks = $search['tracks'];
		$albums = $search['albums'];
		$artists = $search['artists'];

		$total_results = 50;
		$initial_results = (get_search_type($string) == 'track' || get_search_type($string) == 'isrc') ? 50 : 5;

		$i = 0;

		if(is_array($tracks))
		{
			$actions_dialog = array();
			$actions_dialog['title'] = 'Sort by';
			$actions_dialog['actions'][] = array('text' => 'Default', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_tracks', 'default', 3650));
			$actions_dialog['actions'][] = array('text' => 'Popularity', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_tracks', 'popularity', 3650));
			$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_tracks', 'artist', 3650));
			$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_search_tracks', 'title', 3650));

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

					if($sort == 'popularity')
					{
						return strcasecmp($b['popularity'], $a['popularity']);
					}
					else if($sort == 'artist')
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
				$album = $track['album'];
				$album_uri = $track['album_uri'];
				$album_countries = $track['album_countries'];

				if(is_available_in_country($album_countries))
				{
					$i++;

					$details_dialog = array();
					$details_dialog['title'] = hsc($title);
					$details_dialog['details'][] = array('detail' => 'Album', 'value' => $album);
					$details_dialog['details'][] = array('detail' => 'Length', 'value' => $length);
					$details_dialog['details'][] = array('detail' => 'Popularity', 'value' => $popularity);

					$actions_dialog = array();
					$actions_dialog['title'] = hsc($title);
					$actions_dialog['actions'][] = array('text' => 'Add to playlist', 'keys' => array('actions', 'title', 'uri', 'isauthorizedwithspotify'), 'values' => array('hide_dialog add_to_playlist', $title, $uri, is_authorized_with_spotify));
					$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $album_uri));
					$actions_dialog['actions'][] = array('text' => 'Search artist', 'keys' => array('actions', 'string'), 'values' => array('hide_dialog get_search', rawurlencode('artist:"' . $artist . '"')));
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
						<div title="Save to/remove from library" class="actions_div" data-actions="save" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_saved($uri) . '_24_img_div"></div></div>
						<div title="Browse artist" class="actions_div" data-actions="browse_artist" data-uri="' . $artist_uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div artist_24_img_div"></div></div>
						<div title="More" class="show_actions_dialog_div actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
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
				echo '<div class="green_button_div green_button_below_list_div actions_div" data-actions="show_all_list_items" data-items="list_item_track_div" data-highlightclass="light_green_highlight" onclick="void(0)">Show all tracks</div>';
			}

			echo '</div>';
		}

		$i = 0;

		if(!empty($albums))
		{
			echo '<div class="cards_vertical_div"><div class="cards_vertical_title_div">Albums</div>';

			foreach($albums as $album)
			{
				$title = $album['title'];
				$type = $album['type'];
				$uri = $album['uri'];
				$cover_art = $album['cover_art'];

				echo '<div title="' . hsc($title) . '" class="card_vertical_div actions_div" data-actions="browse_album" data-uri="' . $uri . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($title) . '</div><div class="card_vertical_lower_div">' . $type . '</div></div>';
			}

			echo '<div class="clear_float_div"></div></div>';
		}

		if(!empty($artists))
		{
			echo '<div class="cards_vertical_div"><div class="cards_vertical_title_div">Artists</div>';

			foreach($artists as $artist)
			{
				$name = $artist['artist'];
				$popularity = $artist['popularity'];
				$uri = $artist['uri'];
				$cover_art = $artist['cover_art'];

				echo '<div title="' . hsc($name) . '" class="card_vertical_div actions_div" data-actions="browse_artist" data-uri="' . $uri . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($name) . '</div><div class="card_vertical_lower_div">Popularity: ' . hsc($popularity) . '</div></div>';
			}

			echo '<div class="clear_float_div"></div></div>';
		}

		echo '</div>';
	}
}
else
{
	$recent_searches = get_db_rows('search-history', "SELECT string FROM search_history ORDER BY id DESC", array('string'));

	$activity = array();
	$activity['title'] = 'Search';
	$activity['actions'][] = array('action' => array('Clear recent searches', 'delete_32_img_div'), 'keys' => array('actions'), 'values' => array('clear_search_history'));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="search_form_div">
		<form method="post" action="." id="search_form" autocomplete="off" autocapitalize="off">
		<div class="input_text_div"><div><div class="img_div img_24_div search_24_img_div"></div></div><div><input type="text" id="search_input" value="Search..." data-hint="Search..."></div></div>
		<div class="invisible_div"><input type="submit" value="Search"></div>
		</form>
		</div>

		<div class="list_header_div"><div><div>RECENT SEARCHES</div></div><div></div></div>

		<div class="list_div">
	';

	if(empty($recent_searches))
	{
		echo '<div class="list_empty_div">No recent searches.</div>';
	}
	else
	{
		foreach($recent_searches as $recent_search)
		{
			$string = $recent_search['string'];
			$title = get_search_title($string);

			echo '
				<div class="list_item_div">
				<div title="' . hsc($title) . '" class="list_item_main_div actions_div" data-actions="get_search" data-string="' . rawurlencode($string) . '" data-highlightclass="light_grey_highlight" onclick="void(0)">
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div search_24_img_div"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . hsc($title) . '</div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	echo '</div></div>';
}

?>
