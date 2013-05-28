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

if(isset($_GET['star']))
{
	echo star_uri($_POST['type'], rawurldecode($_POST['artist']), rawurldecode($_POST['title']), $_POST['uri']);
}
elseif(isset($_GET['unstar']))
{
	echo unstar_uri($_POST['uri']);
}
elseif(isset($_GET['import_uris']))
{
	echo import_starred_tracks($_POST['uris']);
}
elseif(isset($_GET['import_from_spotify']))
{
	echo import_starred_spotify_tracks();
}
elseif(isset($_GET['import']))
{
	$activity = array();
	$activity['title'] = 'Import starred tracks';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="divider_div"><div><div>CHOOSE OPTION</div></div><div></div></div>

		<div class="list_div">

		<div class="list_item_div">
		<div title="Import manually" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="starred" data-subactivity="import-manually" data-args="" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div edit_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Import manually</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="Import from Spotify" class="list_item_main_div actions_div" data-actions="confirm_import_starred_spotify_tracks" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div import_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Import from Spotify</div></div>
		</div>
		</div>
		</div>

		</div>

		<div class="divider_div"><div><div>INFORMATION</div></div><div></div></div>

		<ul>
		<li>Importing from Spotify is very experimental and may not work</li>
		<li>It is recommended to import manually, see instructions below</li>
		<li>Importing many tracks may take some time because metadata must be looked up on Spotify\'s servers</li>
		<li>Open the desktop Spotify client, go to "Starred", select all tracks (Ctrl + A), right click and choose "Copy Spotify URI"</li>
		<li>Choose manual import above, paste the URIs in the field and save with the return key on the keyboard</li>
		<li>On Android, share a track to ' . project_name . ' from another app to import it</li>
		</ul>

		</div>
	';
}
elseif(isset($_GET['import-manually']))
{
	$activity = array();
	$activity['title'] = 'Import starred tracks manually';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_form_div">
		<form method="post" action="." id="import_starred_tracks_form" autocomplete="off" autocapitalize="off">
		<div class="input_text_div"><div class="input_text_bottom_border_div"></div><div class="input_text_left_border_div"></div><input type="text" id="import_starred_tracks_uris_input" value="URIs..." data-hint="URIs..."><div class="input_text_right_border_div"></div></div>
		<div class="invisible_div"><input type="submit" value="Import"></div>
		</form>
		</div>

		<div class="below_form_div">Separate multiple URIs by space</div>

		</div>
	';
}
else
{
	$activity = array();
	$activity['title'] = 'Starred';
	$activity['actions'] = array('icon' => array('Import tracks', 'import_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'starred', 'import', ''));

	$actions_dialog = array();
	$actions_dialog['title'] = 'Sort by';
	$actions_dialog['actions'][] = array('text' => 'Added', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_starred_tracks', 'default', 36500));
	$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_starred_tracks', 'artist', 36500));
	$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_starred_tracks', 'title', 36500));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="divider_div"><div><div>TRACKS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_starred_tracks') . '_24_img_div"></div></div></div>

		<div class="list_div">
	';

	$sort = $_COOKIE['settings_sort_starred_tracks'];

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

	$tracks = get_db_rows('starred', "SELECT artist, title, uri FROM starred WHERE type = 'track' ORDER BY " . sqlite_escape($order1) . " COLLATE NOCASE " . sqlite_escape($order) . ", " . sqlite_escape($order2) . " COLLATE NOCASE " . sqlite_escape($order), array('artist', 'title', 'uri'));

	if(empty($tracks))
	{
		echo '<div class="list_empty_div">No tracks.</div>';
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
			$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $uri));
			$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
			$actions_dialog['actions'][] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $uri));
			$actions_dialog['actions'][] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
			$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($uri))));

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
				<div title="More by ' . hsc($artist) . '" class="actions_div" data-actions="search_spotify" data-string="' . rawurlencode('artist:"' . $artist . '"') . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div search_24_img_div"></div></div>
				<div title="Remove" class="actions_div" data-actions="unstar_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div remove_24_img_div"></div></div>
				<div title="More" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	$actions_dialog = array();
	$actions_dialog['title'] = 'Sort by';
	$actions_dialog['actions'][] = array('text' => 'Added', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_starred_albums', 'default', 36500));
	$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_starred_albums', 'artist', 36500));
	$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_starred_albums', 'title', 36500));

	echo '
		</div>

		<div class="divider_div"><div><div>ALBUMS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_starred_albums') . '_24_img_div"></div></div></div>

		<div class="list_div">
	';

	$sort = $_COOKIE['settings_sort_starred_albums'];

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

	$albums = get_db_rows('starred', "SELECT artist, title, uri FROM starred WHERE type = 'album' ORDER BY " . sqlite_escape($order1) . " COLLATE NOCASE " . sqlite_escape($order) . ", " . sqlite_escape($order2) . " COLLATE NOCASE " . sqlite_escape($order), array('artist', 'title', 'uri'));

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
			$actions_dialog['actions'][] = array('text' => 'More by ' . hsc($artist), 'keys' => array('actions', 'string'), 'values' => array('hide_dialog search_spotify', rawurlencode('artist:"' . $artist . '"')));
			$actions_dialog['actions'][] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $uri));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));
			$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($uri))));

			echo '
				<div class="list_item_div">
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
				<div title="Remove" class="actions_div" data-actions="unstar_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div remove_24_img_div"></div></div>
				<div title="More" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	echo '</div></div>';
}

?>
