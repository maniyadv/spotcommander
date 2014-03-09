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

$browse_uri = $_GET['uri'];
$browse_uri_type = get_uri_type($browse_uri);

if($browse_uri_type == 'track')
{
	$artist_uri = lookup_track_artist($browse_uri);
}
elseif($browse_uri_type == 'album')
{
	$artist_uri = lookup_album_artist($browse_uri);
}
else
{
	$artist_uri = $browse_uri;
}

$metadata = (empty($artist_uri)) ? null : lookup_artist($artist_uri);

if(empty($metadata))
{
	$activity = array();
	$activity['title'] = 'Error';
	$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Lookup API error. Try again.</div></div>

		</div>
	';
}
else
{
	if($browse_uri_type == 'track') save_track_artist($browse_uri, $artist_uri);

	$artist = $metadata['artist'];
	$artist_uri = $metadata['uri'];
	$tracks = (empty($metadata['tracks'])) ? '' : $metadata['tracks'];
	$albums = (empty($metadata['albums'])) ? '' : $metadata['albums'];
	$appears_on_albums = (empty($metadata['appears_on_albums'])) ? '' : $metadata['appears_on_albums'];
	$albums_count = $metadata['albums_count'];

	$cover_art = cover_art_exists($artist_uri);
	$cover_art_uri = (!$cover_art) ? $artist_uri : '';
	$cover_art_style = (!$cover_art) ? '' : 'background-image: url(\'' . $cover_art . '\')';

	$actions_dialog = array();
	$actions_dialog['title'] = hsc($artist);
	$actions_dialog['actions'][] = array('text' => 'More by ' . hsc($artist), 'keys' => array('actions', 'string'), 'values' => array('hide_dialog search_spotify', rawurlencode('artist:"' . $artist . '"')));
	$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'title', 'uri'), 'values' => array('hide_dialog share_uri', hsc($artist), rawurlencode(uri_to_url($artist_uri))));

	$activity = array();
	$activity['title'] = hsc($artist);
	$activity['cover'] = $cover_art_uri;
	$activity['actions'][] = array('action' => array('Play', ''), 'keys' => array('actions', 'uri'), 'values' => array('play_uri', $artist_uri));
	$activity['actions'][] = array('action' => array('Play randomly', ''), 'keys' => array('actions', 'uri'), 'values' => array('play_uri_randomly', $artist_uri));
	$activity['actions'][] = array('action' => array('More...', ''), 'keys' => array('actions', 'dialogactions'), 'values' => array('show_actions_dialog', base64_encode(json_encode($actions_dialog))));

	echo '
		<div id="cover_art_div">
		<div id="cover_art_art_div" class="actions_div" data-actions="resize_cover_art" style="' . $cover_art_style . '" onclick="void(0)"></div>
		<div id="cover_art_play_div" class="actions_div" data-actions="play_uri" data-uri="' . $artist_uri . '" data-highlightclass="opacity_highlight" onclick="void(0)"></div>
		<div id="cover_art_information_div"><div><div>Artist</div></div><div><div>' . get_albums_count($albums_count) . '</div></div></div>
		</div>

		<div id="activity_inner_div" class="below_cover_art_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">
	';

	$initial_results = 5;

	if(!empty($tracks))
	{
		$sort = $_COOKIE['settings_sort_artist_tracks'];

		function tracks_cmp($a, $b)
		{
			global $sort;

			if($sort == 'title')
			{
				return strcasecmp($a['title'], $b['title']);
			}
		}

		if($sort != 'default') usort($tracks, 'tracks_cmp');

		$actions_dialog = array();
		$actions_dialog['title'] = 'Sort by';
		$actions_dialog['actions'][] = array('text' => 'Popularity', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_tracks', 'default', 36500));
		$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_tracks', 'title', 36500));

		echo '
			<div class="list_header_div"><div><div>TOP TRACKS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_artist_tracks') . '_24_img_div"></div></div></div>

			<div class="list_div">
		';

		$i = 0;

		foreach($tracks as $track)
		{
			$i++;

			$artist = $track['artist'];
			$title = $track['title'];
			$length = $track['length'];
			$uri = $track['uri'];

			$details_dialog = array();
			$details_dialog['title'] = hsc($title);
			$details_dialog['details'][] = array('detail' => 'Length', 'value' => $length);

			$actions_dialog = array();
			$actions_dialog['title'] = hsc($title);
			$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $uri));
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

		if($i > $initial_results) echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_track_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all tracks</div></div></div>';

		echo '</div>';
	}

	if(!empty($albums))
	{
		$sort = $_COOKIE['settings_sort_artist_albums'];

		function albums_cmp($a, $b)
		{
			global $sort;

			if($sort == 'title')
			{
				return strcasecmp($a['title'], $b['title']);
			}
			elseif($sort == 'released')
			{
				return strcasecmp($b['released'], $a['released']);
			}
		}

		if($sort != 'default') usort($albums, 'albums_cmp');

		$actions_dialog = array();
		$actions_dialog['title'] = 'Sort by';
		$actions_dialog['actions'][] = array('text' => 'Default', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_albums', 'default', 36500));
		$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_albums', 'title', 36500));
		$actions_dialog['actions'][] = array('text' => 'Released', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_albums', 'released', 36500));

		echo '
			<div class="list_header_div"><div><div>ALBUMS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_artist_albums') . '_24_img_div"></div></div></div>

			<div class="list_div">
		';

		$i = 0;

		foreach($albums as $album)
		{
			$i++;

			$artist = $album['artist'];
			$artist_uri = $album['artist_uri'];
			$title = $album['title'];
			$released = $album['released'];
			$uri = $album['uri'];

			$details_dialog = array();
			$details_dialog['title'] = hsc($title);
			$details_dialog['details'][] = array('detail' => 'Released', 'value' => $released);

			$actions_dialog = array();
			$actions_dialog['title'] = hsc($title);
			$actions_dialog['actions'][] = array('text' => 'More by ' . hsc($artist), 'keys' => array('actions', 'string'), 'values' => array('hide_dialog search_spotify', rawurlencode('artist:"' . $artist . '"')));
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

		if($i > $initial_results) echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_album_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all albums</div></div></div>';

		echo '</div>';
	}

	if(!empty($appears_on_albums))
	{
		$sort = $_COOKIE['settings_sort_artist_appears_on_albums'];

		function appears_on_albums_cmp($a, $b)
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
			elseif($sort == 'released')
			{
				return strcasecmp($b['released'], $a['released']);
			}
		}

		if($sort != 'default') usort($appears_on_albums, 'appears_on_albums_cmp');

		$actions_dialog = array();
		$actions_dialog['title'] = 'Sort by';
		$actions_dialog['actions'][] = array('text' => 'Default', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_appears_on_albums', 'default', 36500));
		$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_appears_on_albums', 'artist', 36500));
		$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_appears_on_albums', 'title', 36500));
		$actions_dialog['actions'][] = array('text' => 'Released', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_artist_appears_on_albums', 'released', 36500));

		echo '
			<div class="list_header_div"><div><div>APPEARS ON</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_artist_appears_on_albums') . '_24_img_div"></div></div></div>

			<div class="list_div">
		';

		$i = 0;

		foreach($appears_on_albums as $appears_on_album)
		{
			$i++;

			$artist = $appears_on_album['artist'];
			$artist_uri = $appears_on_album['artist_uri'];
			$title = $appears_on_album['title'];
			$released = $appears_on_album['released'];
			$uri = $appears_on_album['uri'];

			$details_dialog = array();
			$details_dialog['title'] = hsc($title);
			$details_dialog['details'][] = array('detail' => 'Released', 'value' => $released);

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
				<div class="list_item_div list_item_appears_on_album_div ' . $class . '">
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

		if($i > $initial_results) echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_appears_on_album_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all albums</div></div></div>';

		echo '</div>';
	}

	echo '</div>';
}

?>
