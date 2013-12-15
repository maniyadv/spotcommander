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

$nowplaying = get_nowplaying();

$playbackstatus = $nowplaying['playbackstatus'];

$play_pause = 'play';
$volume = 50;
$artist = 'Unknown';
$title = 'Spotify is not running';
$album = 'Unknown';
$cover_art = 'img/no-cover-art-640.png?' . project_serial;
$uri = '';
$length = 'Unknown';
$released = 'Unknown';
$popularity = 'Unknown';

$actions = array();
$actions[] = array('action' => array('Recently played', 'history_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'recently-played', '', ''));

if(spotify_is_running())
{
	if($playbackstatus == 'Playing' || $playbackstatus == 'Paused')
	{
		$play_pause = ($playbackstatus == 'Playing') ? 'pause' : 'play';
		$volume = get_current_volume();
		$artist = (empty($nowplaying['artist'])) ? $artist : $nowplaying['artist'];
		$title = (empty($nowplaying['title'])) ? $title : $nowplaying['title'];
		$album = (empty($nowplaying['album'])) ? $album : $nowplaying['album'];
		$album_uri_type = get_uri_type($album);
		$uri = $nowplaying['url'];
		$length = convert_length($nowplaying['length'], 'mc');

		if($album_uri_type == 'playlist')
		{
			$actions = array();
			$actions[] = array('action' => array('Browse playlist', 'browse_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'playlists', 'browse', 'uri=' . $album));

			$title = 'Playlist advertisement';
			$album = 'Unknown';
			$uri = '';
		}
		elseif($album_uri_type == 'album')
		{
			$actions = array();
			$actions[] = array('action' => array('Browse album', 'browse_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'album', '', 'uri=' . $album));

			$title = 'Album advertisement';
			$album = 'Unknown';
			$uri = '';
		}
		elseif(ssw($album, 'spotify:') || ssw($album, 'http://') || ssw($album, 'https://') || get_uri_type($uri) == 'track' && $album == 'Unknown')
		{
			if(ssw($album, 'http://') || ssw($album, 'https://'))
			{
				$actions = array();
				$actions[] = array('action' => array('Open advertisement', 'internet_32_img_div'), 'keys' => array('actions', 'uri'), 'values' => array('open_external_activity', $album));
			}

			$title = 'Advertisement';
			$album = 'Unknown';
			$uri = '';
		}
		else
		{
			if(!empty($nowplaying['artUrl']))
			{
				$cover_art = $nowplaying['artUrl'];
				$cover_art = str_replace(array('open.spotify.com', '/thumb/'), array('o.scdn.co', '/640/'), $cover_art);
				$cover_art = (get_uri_type($cover_art) == 'cover_art') ? $cover_art : 'img/no-cover-art-640.png?' . project_serial;
			}

			if(!empty($nowplaying['contentCreated']))
			{
				$released = $nowplaying['contentCreated'];
				$released = substr($released, 0, 4);
			}

			if(!empty($nowplaying['autoRating']))
			{
				$popularity = $nowplaying['autoRating'];
				$popularity = convert_popularity($popularity);
			}

			if($playbackstatus == 'Playing') save_recently_played($artist, $title, $uri);

			$details_dialog = array();
			$details_dialog['title'] = hsc($title);
			$details_dialog['details'][] = array('detail' => 'Album', 'value' => $album);
			$details_dialog['details'][] = array('detail' => 'Released', 'value' => $released);
			$details_dialog['details'][] = array('detail' => 'Length', 'value' => $length);
			$details_dialog['details'][] = array('detail' => 'Popularity', 'value' => $popularity);

			$actions_dialog = array();
			$actions_dialog['title'] = hsc($title);
			$actions_dialog['actions'][] = array('text' => 'More by ' . hsc($artist), 'keys' => array('actions', 'string'), 'values' => array('hide_dialog search_spotify', rawurlencode('artist:"' . $artist . '"')));
			$actions_dialog['actions'][] = array('text' => 'Browse artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog browse_artist', $uri));
			$actions_dialog['actions'][] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'false'));
			$actions_dialog['actions'][] = array('text' => 'Share', 'keys' => array('actions', 'title', 'uri'), 'values' => array('hide_dialog share_uri', hsc($title), rawurlencode(uri_to_url($uri))));
			$actions_dialog['actions'][] = array('text' => 'YouTube', 'keys' => array('actions', 'uri'), 'values' => array('open_external_activity', 'http://www.youtube.com/results?search_query=' . rawurlencode(strip_string($artist . ' ' . $title))));
			$actions_dialog['actions'][] = array('text' => 'Last.fm', 'keys' => array('actions', 'uri'), 'values' => array('open_external_activity', 'http://www.last.fm/music/' . rawurlencode($artist) . '/_/' . rawurlencode($title)));
			$actions_dialog['actions'][] = array('text' => 'Wikipedia', 'keys' => array('actions', 'uri'), 'values' => array('open_external_activity', 'http://en.wikipedia.org/wiki/Special:Search?search=' . rawurlencode($artist)));

			$actions = array();
			$actions[] = array('action' => array('Recently played', ''), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'recently-played', '', ''));
			$actions[] = array('action' => array('Show queue', ''), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'queue', '', ''));

			if(daemon_pulseaudio_check()) $actions[] = (get_volume_control() == 'spotify') ? array('action' => array('System volume', ''), 'keys' => array('actions', 'volumecontrol'), 'values' => array('adjust_volume_control', 'system')) : array('action' => array('Spotify\'s volume', ''), 'keys' => array('actions', 'volumecontrol'), 'values' => array('adjust_volume_control', 'spotify'));

			$actions[] = array('action' => array('Lyrics', ''), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
			$actions[] = array('action' => array('Queue', ''), 'keys' => array('actions', 'artist', 'title', 'uri'), 'values' => array('queue_uri', rawurlencode($artist), rawurlencode($title), $uri));
			$actions[] = array('action' => array(ucfirst(uri_is_starred($uri)), ''), 'keys' => array('actions', 'type', 'artist', 'title', 'uri'), 'values' => array('star_uri', 'track', rawurlencode($artist), rawurlencode($title), $uri));
			$actions[] = array('action' => array('Details', ''), 'keys' => array('actions', 'dialogdetails'), 'values' => array('show_details_dialog', base64_encode(json_encode($details_dialog))));
			$actions[] = array('action' => array('More...', ''), 'keys' => array('actions', 'dialogactions'), 'values' => array('show_actions_dialog', base64_encode(json_encode($actions_dialog))));
		}
	}
	else
	{
		$title = 'No music is playing';
	}
}

$metadata = array();
$metadata['play_pause'] = $play_pause;
$metadata['artist'] = $artist;
$metadata['title'] = $title;
$metadata['album'] = $album;
$metadata['cover_art'] = $cover_art;
$metadata['uri'] = $uri;
$metadata['tracklength'] = $length;
$metadata['released'] = $released;
$metadata['current_volume'] = $volume;
$metadata['actions'] = $actions;

echo json_encode($metadata);

?>
