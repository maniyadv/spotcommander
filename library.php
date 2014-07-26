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

if(isset($_GET['save']))
{
	echo save(rawurldecode($_POST['artist']), rawurldecode($_POST['title']), $_POST['uri']);
}
elseif(isset($_GET['remove']))
{
	echo remove($_POST['uri']);
}
elseif(isset($_GET['import_saved_spotify_tracks']))
{
	echo import_saved_spotify_tracks();
}
elseif(isset($_GET['remove_all_saved_items']))
{
	echo remove_all_saved_items();
}
else
{
	$activity = array();
	$activity['title'] = 'Library';
	$activity['actions'][] = array('action' => array('Import from Spotify', 'import_32_img_div'), 'keys' => array('actions', 'isauthorizedwithspotify'), 'values' => array('confirm_import_saved_spotify_tracks', is_authorized_with_spotify));

	$actions_dialog = array();
	$actions_dialog['title'] = 'Sort by';
	$actions_dialog['actions'][] = array('text' => 'Added', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_tracks', 'default', 3650));
	$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_tracks', 'artist', 3650));
	$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_tracks', 'title', 3650));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="list_header_div"><div><div>TRACKS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_library_tracks') . '_24_img_div"></div></div></div>

		<div class="list_div">
	';

	$sort = $_COOKIE['settings_sort_library_tracks'];

	$order = 'DESC';
	$order1 = 'id';
	$order2 = 'artist';

	if($sort == 'artist')
	{
		$order = 'ASC';
		$order1 = 'artist';
		$order2 = 'title';
	}
	elseif($sort == 'title')
	{
		$order = 'ASC';
		$order1 = 'title';
		$order2 = 'artist';
	}

	$tracks = get_db_rows('library', "SELECT artist, title, uri FROM library WHERE type = 'track' ORDER BY " . sqlite_escape($order1) . " COLLATE NOCASE " . sqlite_escape($order) . ", " . sqlite_escape($order2) . " COLLATE NOCASE " . sqlite_escape($order), array('artist', 'title', 'uri'));

	if(empty($tracks))
	{
		echo '<div class="list_empty_div">No tracks. Tap the top right icon to import your saved tracks from Spotify.</div>';
	}
	else
	{
		foreach($tracks as $track)
		{
			$artist = $track['artist'];
			$title = $track['title'];
			$uri = $track['uri'];

			$actions_dialog = array();
			$actions_dialog['title'] = hsc($title);
			$actions_dialog['actions'][] = array('text' => 'Add to playlist', 'keys' => array('actions', 'title', 'uri', 'isauthorizedwithspotify'), 'values' => array('hide_dialog add_to_playlist', $title, $uri, is_authorized_with_spotify));
			$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $uri));
			$actions_dialog['actions'][] = array('text' => 'Search artist', 'keys' => array('actions', 'string'), 'values' => array('hide_dialog get_search', rawurlencode('artist:"' . $artist . '"')));
			$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
			$actions_dialog['actions'][] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
			$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'title', 'uri'), 'values' => array('hide_dialog share_uri', hsc($title), rawurlencode(uri_to_url($uri))));

			echo '
				<div class="list_item_div list_item_track_div">
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
				<div title="Browse artist" class="actions_div" data-actions="browse_artist" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div artist_24_img_div"></div></div>
				<div title="Remove" class="actions_div" data-actions="remove" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div delete_24_img_div"></div></div>
				<div title="More" class="show_actions_dialog_div actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	$actions_dialog = array();
	$actions_dialog['title'] = 'Sort by';
	$actions_dialog['actions'][] = array('text' => 'Added', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_albums', 'default', 3650));
	$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_albums', 'artist', 3650));
	$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_albums', 'title', 3650));

	echo '
		</div>

		<div class="list_header_div"><div><div>ALBUMS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_library_albums') . '_24_img_div"></div></div></div>

		<div class="list_div">
	';

	$sort = $_COOKIE['settings_sort_library_albums'];

	$order = 'DESC';
	$order1 = 'id';
	$order2 = 'uri';

	if($sort == 'artist')
	{
		$order = 'ASC';
		$order1 = 'artist';
		$order2 = 'title';
	}
	elseif($sort == 'title')
	{
		$order = 'ASC';
		$order1 = 'title';
		$order2 = 'artist';
	}

	$albums = get_db_rows('library', "SELECT artist, title, uri FROM library WHERE type = 'album' ORDER BY " . sqlite_escape($order1) . " COLLATE NOCASE " . sqlite_escape($order) . ", " . sqlite_escape($order2) . " COLLATE NOCASE " . sqlite_escape($order), array('artist', 'title', 'uri'));

	if(empty($albums))
	{
		echo '<div class="list_empty_div">No albums.</div>';
	}
	else
	{
		foreach($albums as $album)
		{
			$artist = urldecode($album['artist']);
			$title = urldecode($album['title']);
			$uri = $album['uri'];

			$actions_dialog = array();
			$actions_dialog['title'] = hsc($title);
			$actions_dialog['actions'][] = array('text' => 'Browse artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_artist', $uri));
			$actions_dialog['actions'][] = array('text' => 'Search artist', 'keys' => array('actions', 'string'), 'values' => array('hide_dialog get_search', rawurlencode('artist:"' . $artist . '"')));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));
			$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'title', 'uri'), 'values' => array('hide_dialog share_uri', hsc($title), rawurlencode(uri_to_url($uri))));

			echo '
				<div class="list_item_div list_item_album_div">
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
				<div title="Shuffle play" class="actions_div" data-actions="shuffle_play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div shuffle_play_uri_24_img_div"></div></div>
				<div title="Browse" class="actions_div" data-actions="change_activity" data-activity="album" data-subactivity="" data-args="uri=' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div album_24_img_div"></div></div>
				<div title="Remove" class="actions_div" data-actions="remove" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div delete_24_img_div"></div></div>
				<div title="More" class="show_actions_dialog_div actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	$actions_dialog = array();
	$actions_dialog['title'] = 'Sort by';
	$actions_dialog['actions'][] = array('text' => 'Added', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_artists', 'default', 3650));
	$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_library_artists', 'artist', 3650));

	echo '
		</div>

		<div class="list_header_div"><div><div>ARTISTS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_library_artists') . '_24_img_div"></div></div></div>

		<div class="list_div">
	';

	$sort = $_COOKIE['settings_sort_library_artists'];

	$order = 'DESC';
	$order1 = 'id';
	$order2 = 'uri';

	if($sort == 'artist')
	{
		$order = 'ASC';
		$order1 = 'artist';
		$order2 = 'title';
	}

	$artists = get_db_rows('library', "SELECT artist, uri FROM library WHERE type = 'artist' ORDER BY " . sqlite_escape($order1) . " COLLATE NOCASE " . sqlite_escape($order) . ", " . sqlite_escape($order2) . " COLLATE NOCASE " . sqlite_escape($order), array('artist', 'uri'));

	if(empty($artists))
	{
		echo '<div class="list_empty_div">No artists.</div>';
	}
	else
	{
		foreach($artists as $artist)
		{
			$name = urldecode($artist['artist']);
			$uri = $artist['uri'];

			echo '
				<div class="list_item_div list_item_album_div">
				<div title="' . hsc($name) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
				<div class="list_item_main_actions_arrow_div"></div>
				<div class="list_item_main_corner_arrow_div"></div>
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div artist_24_img_div"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . hsc($name) . '</div><div class="list_item_main_inner_text_lower_div">Artist</div></div>
				</div>
				</div>
				<div class="list_item_actions_div">
				<div class="list_item_actions_inner_div">
				<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
				<div title="Shuffle play" class="actions_div" data-actions="shuffle_play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div shuffle_play_uri_24_img_div"></div></div>
				<div title="Browse" class="actions_div" data-actions="browse_artist" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div artist_24_img_div"></div></div>
				<div title="Share" class="actions_div" data-actions="share_uri" data-title="' . hsc($name) . '" data-uri="' . rawurlencode(uri_to_url($uri)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div share_24_img_div"></div></div>
				<div title="Remove" class="actions_div" data-actions="remove" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div delete_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	echo '</div></div>';
}

?>
