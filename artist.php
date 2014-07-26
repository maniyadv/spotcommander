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

if(isset($_GET['get_biography']))
{
	$files = get_external_files(array(project_website . 'api/1/artist/biography/?artist=' . rawurlencode(rawurldecode($_POST['artist']))), null, null);
	$metadata = json_decode($files[0], true);

	if(empty($metadata))
	{
		echo 'error';
	}
	else
	{
		if(empty($metadata['biography']))
		{
			echo 'no_biography';
		}
		else
		{
			$from = (empty($metadata['from'])) ? 'Unknown' : $metadata['from'];
			$formed = (empty($metadata['formed'])) ? 'Unknown' : $metadata['formed'];
			$members = $metadata['members'];
			$listeners = (empty($metadata['listeners'])) ? 'Unknown' : number_format($metadata['listeners'], 0, '.', ',');
			$plays = (empty($metadata['plays'])) ? 'Unknown' : number_format($metadata['plays'], 0, '.', ',');
			$biography = (empty($metadata['biography'])) ? 'Unknown' : $metadata['biography'];

			echo '
				<div><b>From:</b> ' . $from . '</div>
				<div><b>Formed:</b> ' . $formed . '</div>
			';

			if(!empty($members)) echo '<div><b>Members:</b> ' . $members . '</div>';

			echo '
				<div><b>Listeners:</b> ' . $listeners . '</div>
				<div><b>Plays:</b> ' . $plays . '</div>
				<div>' . $biography . '</div>
			';
		}
	}
}
else
{
	$browse_uri = $_GET['uri'];
	$browse_uri_type = get_uri_type($browse_uri);

	if($browse_uri_type == 'track')
	{
		$artist_uri = get_track_artist($browse_uri);
	}
	elseif($browse_uri_type == 'album')
	{
		$artist_uri = get_album_artist($browse_uri);
	}
	else
	{
		$artist_uri = $browse_uri;
	}

	$metadata = (empty($artist_uri)) ? null : get_artist($artist_uri);

	if(empty($metadata))
	{
		$activity = array();
		$activity['title'] = 'Error';
		$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get artist. Try again.</div></div>

			</div>
		';
	}
	else
	{
		if($browse_uri_type == 'track') save_track_artist($browse_uri, $artist_uri);

		$artist = $metadata['artist'];
		$artist_uri = $metadata['uri'];
		$popularity = $metadata['popularity'];
		$cover_art_uri = $metadata['cover_art_uri'];
		$cover_art_width = $metadata['cover_art_width'];
		$cover_art_height = $metadata['cover_art_height'];
		$tracks = $metadata['tracks'];
		$albums = $metadata['albums'];
		$related_artists = $metadata['related_artists'];
		$albums_count = $metadata['albums_count'];

		$activity = array();
		$activity['title'] = hsc($artist);

		if(empty($tracks) && empty($albums) && empty($related_artists))
		{
			echo '
				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Empty artist</div></div>

				</div>
			';
		}
		else
		{
			$details_dialog = array();
			$details_dialog['title'] = hsc($artist);
			$details_dialog['details'][] = array('detail' => 'Popularity', 'value' => $popularity);

			$activity['cover_art_uri'] = '';
			$activity['actions'][] = array('action' => array('Play', ''), 'keys' => array('actions', 'uri'), 'values' => array('play_uri', $artist_uri));
			$activity['actions'][] = array('action' => array('Shuffle play', ''), 'keys' => array('actions', 'uri'), 'values' => array('shuffle_play_uri', $artist_uri));
			$activity['actions'][] = array('action' => array(ucfirst(is_saved($artist_uri)), ''), 'keys' => array('actions', 'artist', 'title', 'uri', 'isauthorizedwithspotify'), 'values' => array('save', rawurlencode($artist), '', $artist_uri, is_authorized_with_spotify));
			$activity['actions'][] = array('action' => array('Search artist', ''), 'keys' => array('actions', 'string'), 'values' => array('get_search', rawurlencode('artist:"' . $artist . '"')));
			$activity['actions'][] = array('action' => array('Details', ''), 'keys' => array('actions', 'dialogdetails'), 'values' => array('show_details_dialog', base64_encode(json_encode($details_dialog))));
			$activity['actions'][] = array('action' => array('Share'), 'keys' => array('actions', 'title', 'uri'), 'values' => array('share_uri', hsc($artist), rawurlencode(uri_to_url($artist_uri))));

			$albums_count = ($albums_count == 1) ? $albums_count . ' album' : $albums_count . ' albums';

			echo '
				<div id="cover_art_div">
				<div id="cover_art_art_div" class="actions_div" data-actions="resize_cover_art" data-resized="false" data-width="' . $cover_art_width . '" data-height="' . $cover_art_height . '" style="background-image: url(\'' . $cover_art_uri . '\')" onclick="void(0)"></div>
				<div id="cover_art_actions_div"><div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $artist_uri . '" data-highlightclass="green_opacity_highlight"><div class="img_div img_32_div cover_art_play_32_img_div"></div></div><div title="Shuffle play" class="actions_div" data-actions="shuffle_play_uri" data-uri="' . $artist_uri . '" data-highlightclass="green_opacity_highlight"><div class="img_div img_32_div cover_art_shuffle_play_32_img_div"></div></div></div>
				<div id="cover_art_information_div"><div><div>Artist</div></div><div><div>' . $albums_count . '</div></div></div>
				</div>

				<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

				<div id="green_artist_biography_button_div" class="green_button_div green_button_below_cover_art_div actions_div" data-actions="get_artist_biography" data-artist="' . rawurlencode($artist) . '" data-highlightclass="light_green_highlight" onclick="void(0)">Biography</div>

				<div id="artist_biography_div"></div>
			';

			if(!empty($tracks))
			{
				echo '
					<div class="list_header_div"><div><div>TOP TRACKS</div></div><div></div></div>

					<div class="list_div">
				';

				$i = 0;

				foreach($tracks as $track)
				{
					$i++;

					$artist = $track['artist'];
					$title = $track['title'];
					$length = $track['length'];
					$popularity = $track['popularity'];
					$uri = $track['uri'];
					$album = $track['album'];
					$album_uri = $track['album_uri'];

					$details_dialog = array();
					$details_dialog['title'] = hsc($title);
					$details_dialog['details'][] = array('detail' => 'Album', 'value' => $album);
					$details_dialog['details'][] = array('detail' => 'Length', 'value' => $length);
					$details_dialog['details'][] = array('detail' => 'Popularity', 'value' => $popularity);

					$actions_dialog = array();
					$actions_dialog['title'] = hsc($title);
					$actions_dialog['actions'][] = array('text' => 'Add to playlist', 'keys' => array('actions', 'title', 'uri', 'isauthorizedwithspotify'), 'values' => array('hide_dialog add_to_playlist', $title, $uri, is_authorized_with_spotify));
					$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
					$actions_dialog['actions'][] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
					$actions_dialog['actions'][] = array('text' => 'Details', 'keys' => array('actions', 'dialogdetails'), 'values' => array('hide_dialog show_details_dialog', base64_encode(json_encode($details_dialog))));
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
						<div title="Save to/remove from library" class="actions_div" data-actions="save" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-isauthorizedwithspotify="' . boolean_to_string(is_authorized_with_spotify) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . is_saved($uri) . '_24_img_div"></div></div>
						<div title="Browse album" class="actions_div" data-actions="browse_album" data-uri="' . $album_uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div album_24_img_div"></div></div>
						<div title="More" class="show_actions_dialog_div actions_div" data-actions="show_actions_dialog" data-dialogactions="' . base64_encode(json_encode($actions_dialog)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
						</div>
						</div>
						</div>
					';
				}

				echo '</div>';
			}

			if(!empty($albums))
			{
				echo '<div class="cards_vertical_div"><div class="cards_vertical_title_div">Albums</div>';

				foreach($albums as $album)
				{
					$title = $album['title'];
					$type = $album['type'];
					$uri = $album['uri'];
					$cover_art = $album['cover_art'];

					echo '<div title="' . hsc($title) . '" class="card_vertical_div actions_div" data-actions="browse_album" data-uri="' . $uri . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($title) . '</div><div class="card_vertical_lower_div">' . hsc($type) . '</div></div>';
				}

				echo '<div class="clear_float_div"></div></div>';
			}

			if(!empty($related_artists))
			{
				echo '<div class="cards_vertical_div"><div class="cards_vertical_title_div">Related artists</div>';

				foreach($related_artists as $related_artist)
				{
					$artist = $related_artist['artist'];
					$popularity = $related_artist['popularity'];
					$uri = $related_artist['uri'];
					$cover_art = $related_artist['cover_art'];

					echo '<div title="' . hsc($artist) . '" class="card_vertical_div actions_div" data-actions="browse_artist" data-uri="' . $uri . '" data-highlightclass="card_highlight" onclick="void(0)"><div class="card_vertical_cover_art_div" style="background-image: url(\'' . $cover_art . '\')"></div><div class="card_vertical_upper_div">' . hsc($artist) . '</div><div class="card_vertical_lower_div">Popularity: ' . hsc($popularity) . '</div></div>';
				}

				echo '<div class="clear_float_div"></div></div>';
			}

			echo '</div>';
		}
	}
}

?>
