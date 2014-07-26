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
along with SpcotCommander.  If not, see <http://www.gnu.org/licenses/>.

*/

require_once('main.php');

if(isset($_GET['import_playlists']))
{
	echo import_playlists($_POST['uris']);
}
elseif(isset($_GET['import_spotify_playlists']))
{
	echo import_spotify_playlists();
}
elseif(isset($_GET['create_playlist']))
{
	echo create_playlist($_POST['name'], string_to_boolean($_POST['make_public']));
}
elseif(isset($_GET['add_uris_to_playlist']))
{
	echo add_uris_to_playlist($_POST['uri'], $_POST['uris']);
}
elseif(isset($_GET['remove_playlist']))
{
	echo remove_playlist($_POST['id']);
}
elseif(isset($_GET['remove_all_playlists']))
{
	echo remove_all_playlists();
}
elseif(isset($_GET['get_playlists_with_starred']))
{
	echo json_encode(get_playlists_with_starred());
}
elseif(isset($_GET['add']))
{
	$activity = array();
	$activity['title'] = 'Add playlists';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="cards_div">
		<div>
		<div>
		<div class="card_div actions_div" data-actions="confirm_import_spotify_playlists" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div import_24_img_div"></div></div><div class="card_text_div"><div><b>Import from Spotify</b></div><div>Import your playlists from Spotify. Repeat this whenever you have created or followed playlists outside of ' . project_name . '.</div></div></div>
		<div class="card_div actions_div" data-actions="change_activity_if_is_authorized_with_spotify" data-activity="playlists" data-subactivity="create" data-args="" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div new_24_img_div"></div></div><div class="card_text_div"><div>Create playlist</div><div>Create a new playlist.</div></div></div>
		</div>
		<div>
		<div class="card_div actions_div" data-actions="change_activity_if_is_authorized_with_spotify" data-activity="playlists" data-subactivity="import" data-args="" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div edit_24_img_div"></div></div><div class="card_text_div"><div>Import manually</div><div>Import playlists manually by pasting Spotify URIs. On Android you can share playlists to ' . project_name . ' from other apps.</div></div></div>
		<div class="card_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="" data-args="" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_icon_div"><div class="img_div img_24_div discover_24_img_div"></div></div><div class="card_text_div"><div>Discover playlists</div><div>Browse popular playlists.</div></div></div>
		</div>
		</div>
		</div>
	';
}
elseif(isset($_GET['import']))
{
	$activity = array();
	$activity['title'] = 'Import playlists manually';
	$activity['actions'][] = array('action' => array('Create', 'yes_32_img_div'), 'keys' => array('actions', 'form'), 'values' => array('submit_form', 'form#import_playlists_form'));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_form_div">
		<form method="post" action="." id="import_playlists_form" autocomplete="off" autocapitalize="off">
		<div class="input_text_div"><div><div class="img_div img_24_div edit_24_img_div"></div></div><div><input type="text" id="import_playlists_uris_input" value="URIs..." data-hint="URIs..."></div></div>
		<div class="input_information_div">Separate multiple URIs by space</div>
		<div class="invisible_div"><input type="submit" value="Import"></div>
		</form>
		</div>

		</div>
	';
}
elseif(isset($_GET['create']))
{
	$activity = array();
	$activity['title'] = 'Create playlist';
	$activity['actions'][] = array('action' => array('Create', 'yes_32_img_div'), 'keys' => array('actions', 'form'), 'values' => array('submit_form', 'form#create_playlist_form'));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_form_div">
		<form method="post" action="." id="create_playlist_form" autocomplete="off" autocapitalize="off">
		<div class="input_text_div"><div><div class="img_div img_24_div edit_24_img_div"></div></div><div><input type="text" id="create_playlist_name_input" value="Name..." data-hint="Name..."></div></div>
		<div class="input_checkbox_div"><div><input type="checkbox" id="create_playlist_make_public_input"></div><div><label for="create_playlist_make_public_input">Make public</label></div></div>
		<div class="invisible_div"><input type="submit" value="Create"></div>
		</form>
		</div>

		</div>
	';
}
elseif(isset($_GET['browse']))
{
	$uri = str_replace('%3A', ':', rawurlencode($_GET['uri']));
	$metadata = get_playlist($uri);

	if(empty($metadata))
	{
		$activity = array();
		$activity['title'] = 'Error';
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get playlist. Try again.</div></div>

			</div>
		';
	}
	else
	{
		$name = $metadata['name'];
		$user = $metadata['user'];
		$public = $metadata['public'];
		$tracks = (empty($metadata['tracks'])) ? null : $metadata['tracks'];
		$tracks_count = $metadata['tracks_count'];
		$total_length = $metadata['total_length'];

		$activity = array();
		$activity['title'] = ucfirst(hsc($name));

		if(empty($tracks))
		{
			$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Empty playlist</div></div>

				</div>
			';
		}
		else
		{
			$is_starred = (get_uri_type($uri) == 'starred');

			$cover_art_uri = ($is_starred) ? $tracks[0]['uri'] : $uri;

			$cover_art = cover_art_exists($cover_art_uri);
			$cover_art_uri = (!$cover_art) ? $cover_art_uri : '';
			$cover_art_style = (!$cover_art) ? '' : 'background-image: url(\'' . $cover_art . '\')';

			$actions_dialog = array();
			$actions_dialog['title'] = ucfirst(hsc($name));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));

			$details_dialog = array();
			$details_dialog['title'] = ucfirst(hsc($name));
			$details_dialog['details'][] = array('detail' => 'Total length', 'value' => $total_length);
			$details_dialog['details'][] = array('detail' => 'Public', 'value' => $public);

			$activity['cover_art_uri'] = $cover_art_uri;
			$activity['actions'][] = array('action' => array('Play', ''), 'keys' => array('actions', 'uri'), 'values' => array('play_uri', $uri));
			$activity['actions'][] = array('action' => array('Shuffle play', ''), 'keys' => array('actions', 'uri'), 'values' => array('shuffle_play_uri', $uri));
			$activity['actions'][] = array('action' => array('Share', ''), 'keys' => array('actions', 'title', 'uri'), 'values' => array('share_uri', ucfirst(hsc($name)), rawurlencode(uri_to_url($uri))));
			$activity['actions'][] = array('action' => array('Details', ''), 'keys' => array('actions', 'dialogdetails'), 'values' => array('show_details_dialog', base64_encode(json_encode($details_dialog))));
			$activity['actions'][] = array('action' => array('More...', ''), 'keys' => array('actions', 'dialogactions'), 'values' => array('show_actions_dialog', base64_encode(json_encode($actions_dialog))));

			$tracks_count = ($tracks_count == 1) ? $tracks_count . ' track' : $tracks_count . ' tracks';

			echo '
				<div id="cover_art_div">
				<div id="cover_art_art_div" class="actions_div" data-actions="resize_cover_art" data-resized="false" data-width="640" data-height="640" style="' . $cover_art_style . '" onclick="void(0)"></div>
				<div id="cover_art_actions_div"><div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="green_opacity_highlight"><div class="img_div img_32_div cover_art_play_32_img_div"></div></div><div title="Shuffle play" class="actions_div" data-actions="shuffle_play_uri" data-uri="' . $uri . '" data-highlightclass="green_opacity_highlight"><div class="img_div img_32_div cover_art_shuffle_play_32_img_div"></div></div></div>
				<div id="cover_art_information_div"><div><div>Playlist by ' . hsc(is_facebook_user($user)) . '</div></div><div><div>' . $tracks_count . '</div></div></div>
				</div>

				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">
			';


			if(!$is_starred && !playlist_is_saved($uri)) echo '<div class="green_button_div green_button_below_cover_art_div actions_div" data-actions="import_playlist" data-uri="' . $uri . '" data-highlightclass="light_green_highlight" onclick="void(0)">Add to my playlists</div>';

			$actions_dialog = array();
			$actions_dialog['title'] = 'Sort by';
			$actions_dialog['actions'][] = array('text' => 'Track order', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlist_tracks', 'default', 3650));
			$actions_dialog['actions'][] = array('text' => 'Added', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlist_tracks', 'added', 3650));
			$actions_dialog['actions'][] = array('text' => 'Added by', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlist_tracks', 'added_by', 3650));
			$actions_dialog['actions'][] = array('text' => 'Artist', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlist_tracks', 'artist', 3650));
			$actions_dialog['actions'][] = array('text' => 'Title', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlist_tracks', 'title', 3650));

			echo '
				<div class="list_header_div"><div><div>ALL</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_playlist_tracks') . '_24_img_div"></div></div></div>

				<div class="list_div">
			';

			$sort = $_COOKIE['settings_sort_playlist_tracks'];

			if($sort != 'default')
			{
				function tracks_cmp($a, $b)
				{
					global $sort;

					if($sort == 'added')
					{
						return strcasecmp($b['added'], $a['added']);
					}
					else if($sort == 'added_by')
					{
						return strcasecmp($a['added_by'], $b['added_by']);
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

			$i = 0;

			foreach($tracks as $track)
			{
				$i++;

				$artist = $track['artist'];
				$title = $track['title'];
				$length = $track['length'];
				$uri = $track['uri'];
				$added = $track['added'];
				$added_by = $track['added_by'];

				$details_dialog = array();
				$details_dialog['title'] = hsc($title);
				$details_dialog['details'][] = array('detail' => 'Length', 'value' => $length);
				$details_dialog['details'][] = array('detail' => 'Added', 'value' => $added);
				$details_dialog['details'][] = array('detail' => 'Added by', 'value' => $added_by);

				$actions_dialog = array();
				$actions_dialog['title'] = hsc($title);
				$actions_dialog['actions'][] = array('text' => 'Add to playlist', 'keys' => array('actions', 'title', 'uri', 'isauthorizedwithspotify'), 'values' => array('hide_dialog add_to_playlist', $title, $uri, is_authorized_with_spotify));
				$actions_dialog['actions'][] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $uri));
				$actions_dialog['actions'][] = array('text' => 'Search artist', 'keys' => array('actions', 'string'), 'values' => array('hide_dialog get_search', rawurlencode('artist:"' . $artist . '"')));
				$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
				$actions_dialog['actions'][] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
				$actions_dialog['actions'][] = array('text' => 'Details', 'keys' => array('actions', 'dialogdetails'), 'values' => array('hide_dialog show_details_dialog', base64_encode(json_encode($details_dialog))));
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
		}

		echo '</div></div>';
	}
}
else
{
	$activity = array();
	$activity['title'] = 'Playlists';
	$activity['actions'][] = array('action' => array('Add playlists', 'new_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'playlists', 'add', ''));

	echo '<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">';

	$username = get_spotify_username();

	if(!empty($username))
	{
		$uri = 'spotify:user:' . $username . ':starred';

		$actions_dialog = array();
		$actions_dialog['title'] = 'Starred';
		$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
		$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));

		echo '
			<div class="list_header_div"><div><div>STARRED</div></div><div></div></div>

			<div class="list_div">

			<div class="list_item_div">
			<div title="Starred" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
			<div class="list_item_main_actions_arrow_div"></div>
			<div class="list_item_main_corner_arrow_div"></div>
			<div class="list_item_main_inner_div">
			<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div star_24_img_div"></div></div>
			<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Starred</div><div class="list_item_main_inner_text_lower_div">' . $username . '</div></div>
			</div>
			</div>
			<div class="list_item_actions_div">
			<div class="list_item_actions_inner_div">
			<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
			<div title="Shuffle play" class="actions_div" data-actions="shuffle_play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div shuffle_play_uri_24_img_div"></div></div>
			<div title="Browse" class="actions_div" data-actions="browse_playlist" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div playlist_24_img_div"></div></div>
			<div title="Share" class="actions_div" data-actions="share_uri" data-title="Starred" data-uri="' . rawurlencode(uri_to_url($uri)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div share_24_img_div"></div></div>
			<div title="More" class="show_actions_dialog_div actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
			</div>
			</div>
			</div>

			</div>
		';
	}

	$actions_dialog = array();
	$actions_dialog['title'] = 'Sort by';
	$actions_dialog['actions'][] = array('text' => 'Default', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlists', 'default', 3650));
	$actions_dialog['actions'][] = array('text' => 'Name', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlists', 'name', 3650));
	$actions_dialog['actions'][] = array('text' => 'User', 'keys' => array('actions', 'cookieid', 'cookievalue', 'cookieexpires'), 'values' => array('hide_dialog set_cookie refresh_activity', 'settings_sort_playlists', 'user', 3650));

	echo '
		<div class="list_header_div"><div><div>PLAYLISTS</div></div><div title="Sort" class="actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_sorted('settings_sort_playlists') . '_24_img_div"></div></div></div>

		<div class="list_div">
	';

	$sort = $_COOKIE['settings_sort_playlists'];

	$order1 = 'id';
	$order2 = 'uri';

	if($sort == 'name')
	{
		$order1 = 'name';
		$order2 = 'uri';
	}
	elseif($sort == 'user')
	{
		$order1 = 'uri';
		$order2 = 'name';
	}

	$playlists = get_playlists($order1, $order2);

	if(empty($playlists))
	{
		echo '<div class="list_empty_div">No playlists. <span class="actions_span" data-actions="change_activity" data-activity="playlists" data-subactivity="add" data-args="" onclick="void(0)">Tap here</span> to import your playlists.</div>';
	}
	else
	{
		foreach($playlists as $playlist)
		{
			$id = $playlist['id'];
			$name = $playlist['name'];
			$uri = $playlist['uri'];
			$user = explode(':', $uri);
			$user = is_facebook_user(urldecode($user[2]));

			$name = ($name == 'Unknown') ? 'Unknown (ID: ' . $id . ')' : $name;

			$actions_dialog = array();
			$actions_dialog['title'] = ucfirst(hsc($name));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
			$actions_dialog['actions'][] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uris', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));
			$actions_dialog['actions'][] = array('text' => 'Remove', 'keys' => array('actions', 'id'), 'values' => array('hide_dialog remove_playlist', $id));

			echo '
				<div class="list_item_div">
				<div title="' . ucfirst(hsc($name)) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
				<div class="list_item_main_actions_arrow_div"></div>
				<div class="list_item_main_corner_arrow_div"></div>
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div playlist_24_img_div"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">' . ucfirst(hsc($name)) . '</div><div class="list_item_main_inner_text_lower_div">' . hsc($user) . '</div></div>
				</div>
				</div>
				<div class="list_item_actions_div">
				<div class="list_item_actions_inner_div">
				<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
				<div title="Shuffle play" class="actions_div" data-actions="shuffle_play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div shuffle_play_uri_24_img_div"></div></div>
				<div title="Browse" class="actions_div" data-actions="browse_playlist" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div playlist_24_img_div"></div></div>
				<div title="Share" class="actions_div" data-actions="share_uri" data-title="' . ucfirst(hsc($name)) . '" data-uri="' . rawurlencode(uri_to_url($uri)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div share_24_img_div"></div></div>
				<div title="More" class="show_actions_dialog_div actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	echo '</div></div>';
}

?>