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

if(isset($_GET['add_uris']))
{
	echo add_playlists($_POST['uris']);
}
elseif(isset($_GET['add_from_spotify']))
{
	echo add_spotify_playlists();
}
elseif(isset($_GET['remove']))
{
	echo remove_playlist($_POST['id']);
}
elseif(isset($_GET['add']))
{
	$activity = array();
	$activity['title'] = 'Add playlists';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="divider_div">CHOOSE OPTION</div>

		<div class="list_div">

		<div class="list_item_div">
		<div title="Add from Spotify" class="list_item_main_div actions_div" data-actions="confirm_add_spotify_playlists" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div add_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Add from Spotify</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="Discover playlists" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="" data-args="" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div discover_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Discover playlists</div></div>
		</div>
		</div>
		</div>

		<div class="list_item_div">
		<div title="Add manually" class="list_item_main_div actions_div" data-actions="change_activity" data-activity="playlists" data-subactivity="add-manually" data-args="" onclick="void(0)">
		<div class="list_item_main_inner_div">
		<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div edit_24_img_div"></div></div>
		<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div">Add manually</div></div>
		</div>
		</div>
		</div>

		</div>

		<div class="divider_div">INFORMATION</div>

		<ul>
		<li>Some users experience that their playlists are not listed automatically with the latest Spotify client</li>
		<li>In that case, playlists can be added easily</li>
		<li>Before adding, <span class="actions_span" data-actions="open_external_activity" data-uri="' . project_website . '?adding_playlists" data-highlightclass="opacity_highlight" onclick="void(0)">read this</span></li>
		<li>Adding can take some time because playlist names must be looked up on Spotify\'s servers</li>
		<li>Playlists can also be added manually by copying Spotify playlist URIs or HTTP links from Spotify or web pages</li>
		<li>On Android, share a playlist to ' . project_name . ' from another app to add it</li>
		</ul>

		</div>
	';
}
elseif(isset($_GET['add-manually']))
{
	$activity = array();
	$activity['title'] = 'Add playlists manually';

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_form_div">
		<form method="post" action="." id="add_playlists_form" autocomplete="off" autocapitalize="off">
		<div class="input_text_div"><div class="input_text_bottom_border_div"></div><div class="input_text_left_border_div"></div><input type="text" id="add_playlists_uris_input" value="URIs..." data-hint="URIs..."><div class="input_text_right_border_div"></div></div>
		<div class="invisible_div"><input type="submit" value="Add"></div>
		</form>
		</div>

		<div class="below_form_div">Separate multiple URIs by space</div>

		</div>
	';
}
elseif(isset($_GET['browse']))
{
	$uri = str_replace('%3A', ':', rawurlencode($_GET['uri']));
	$metadata = get_playlist($uri);
	$initial_results = 20;

	if(empty($metadata))
	{
		$activity = array();
		$activity['title'] = 'Error';
		$activity['actions'] = array('icon' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

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
		$tracks = $metadata['tracks'];
		$count = count($tracks);

		$dialog_actions = array();

		if(!playlist_is_saved($uri)) $dialog_actions[] = array('text' => 'Add to my playlists', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog add_playlist', $uri));

		$dialog_actions[] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
		$dialog_actions[] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));

		$activity = array();
		$activity['title'] = ucfirst($name);
		$activity['cover_art'] = $uri;
		$activity['actions'] = array('icon' => array('More', 'overflow_32_img_div'), 'keys' => array('actions'), 'values' => array('show_activity_overflow_actions'));
		$activity['overflow_actions'][] = array('text' => 'Play', 'keys' => array('actions', 'uri'), 'values' => array('play_uri', $uri));
		$activity['overflow_actions'][] = array('text' => 'Play randomly', 'keys' => array('actions', 'uri'), 'values' => array('play_uri_randomly', $uri));
		$activity['overflow_actions'][] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('share_uri', rawurlencode(uri_to_url($uri))));
		$activity['overflow_actions'][] = array('text' => 'More...', 'keys' => array('actions', 'dialogactions'), 'values' => array('show_dialog_actions', base64_encode(json_encode($dialog_actions))));

		echo '
			<div id="cover_art_div">
			<div id="cover_art_art_div" class="actions_div" data-actions="resize_cover_art" onclick="void(0)"></div>
			<div id="cover_art_play_div" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="opacity_highlight" onclick="void(0)"></div>
			<div id="cover_art_information_div"><div><div>Playlist by ' . hsc($user) . '</div></div><div><div>' . get_tracks_count($count) . '</div></div></div>
			</div>

			<div id="activity_inner_div" class="below_cover_art_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="divider_div">ALL</div>

			<div class="list_div">
		';

		$i = 0;

		foreach($tracks as $track)
		{
			$i++;

			$artist = $track['artist'];
			$title = $track['title'];
			$uri = $track['uri'];

			$dialog_actions = array();
			$dialog_actions[] = array('text' => 'Browse album', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_album', $uri));
			$dialog_actions[] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
			$dialog_actions[] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $uri));
			$dialog_actions[] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
			$dialog_actions[] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($uri))));

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
				<div title="More" class="actions_div" data-actions="show_dialog_actions" data-dialogactions="' . base64_encode(json_encode($dialog_actions)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
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
	$activity['title'] = 'Playlists';
	$activity['actions'] = array('icon' => array('Add playlists', 'add_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'playlists', 'add', ''));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="divider_div">' . strtoupper(project_name) . '</div>

		<div class="list_div">
	';

	$playlists = get_db_rows('playlists', "SELECT * FROM playlists ORDER BY name COLLATE NOCASE, uri", array('id', 'name', 'uri'));

	if(empty($playlists))
	{
		echo '<div class="list_empty_div">No playlists added.</div>';
	}
	else
	{
		foreach($playlists as $playlist)
		{
			$id = $playlist['id'];
			$name = $playlist['name'];
			$uri = $playlist['uri'];
			$user = explode(':', $uri);
			$user = urldecode($user[2]);
			$user = is_facebook_user($user);

			$name = ($name == 'Unknown') ? 'Unknown (ID: ' . $id . ')' : $name;

			$dialog_actions = array();
			$dialog_actions[] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
			$dialog_actions[] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));
			$dialog_actions[] = array('text' => 'Remove', 'keys' => array('actions', 'id'), 'values' => array('hide_dialog remove_playlist', $id));

			echo '
				<div class="list_item_div">
				<div title="' . hsc($name) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
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
				<div title="Play randomly" class="actions_div" data-actions="play_uri_randomly" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_uri_randomly_24_img_div"></div></div>
				<div title="Browse" class="actions_div" data-actions="change_activity" data-activity="playlists" data-subactivity="browse" data-args="uri=' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div browse_24_img_div"></div></div>
				<div title="Share" class="actions_div" data-actions="share_uri" data-uri="' . rawurlencode(uri_to_url($uri)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div share_24_img_div"></div></div>
				<div title="More" class="actions_div" data-actions="show_dialog_actions" data-dialogactions="' . base64_encode(json_encode($dialog_actions)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}
	}

	$playlists = get_spotify_playlists();

	echo '
		</div>

		<div class="divider_div">SPOTIFY</div>

		<div class="list_div">
	';

	if(empty($playlists))
	{
		echo '<div class="list_empty_div">No playlists. <span class="actions_span" data-actions="change_activity" data-activity="playlists" data-subactivity="add" data-args="" data-highlightclass="opacity_highlight" onclick="void(0)">Learn more</span>.</div>';
	}
	else
	{
		natcasesort($playlists);

		foreach($playlists as $uri => $name)
		{
			$user = explode(':', $uri);
			$user = urldecode($user[2]);
			$user = is_facebook_user($user);

			$dialog_actions = array();
			$dialog_actions[] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'false'));
			$dialog_actions[] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $uri, 'true'));

			echo '
				<div class="list_item_div">
				<div title="' . hsc($name) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
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
				<div title="Play randomly" class="actions_div" data-actions="play_uri_randomly" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_uri_randomly_24_img_div"></div></div>
				<div title="Browse" class="actions_div" data-actions="change_activity" data-activity="playlists" data-subactivity="browse" data-args="uri=' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div browse_24_img_div"></div></div>
				<div title="Share" class="actions_div" data-actions="share_uri" data-uri="' . rawurlencode(uri_to_url($uri)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div share_24_img_div"></div></div>
				<div title="More" class="actions_div" data-actions="show_dialog_actions" data-dialogactions="' . base64_encode(json_encode($dialog_actions)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';			
		}
	}

	echo '</div></div>';
}

?>
