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

// Spotify

function spotify_is_running()
{
	$commands = array('spotify', 'spotify-bin', 'spotify-client');

	foreach($commands as $command)
	{
		if(trim(shell_exec('pgrep -x ' . $command . ' 1>/dev/null 2>&1 && echo 1')) == 1) return true;
	}

	return false;
}

function get_spotify_username()
{
	return remote_control('get_spotify_username', '');
}

// Daemon

function daemon_start($user)
{
	daemon_stop();

	sleep(2);

	$qdbus = daemon_qdbus_select();

	$log_file = __DIR__ . '/run/daemon.log';
	$user_file = __DIR__ . '/run/daemon.user';
	$inotifywait_file = __DIR__ . '/run/daemon.inotify';

	file_write($log_file, '');
	file_write($user_file, $user);

	exec(__DIR__ . '/bin/spotcommander-remote 1>>' . $log_file . ' 2>> ' . $log_file . ' &');
	exec(__DIR__ . '/bin/spotcommander-inotifywait ' . $inotifywait_file . ' 1>>' . $log_file . ' 2>> ' . $log_file . ' &');
	exec(__DIR__ . '/bin/spotcommander-queue "' . __DIR__ . '" "' . $qdbus . '" 1>>' . $log_file . ' 2>> ' . $log_file . ' &');
}

function daemon_stop()
{
	$inotifywait_file = __DIR__ . '/run/daemon.inotify';
	$dbus_monitor_watch_expressions = 'type=\'signal\',path=\'/org/mpris/MediaPlayer2\',interface=\'org.freedesktop.DBus.Properties\',member=\'PropertiesChanged\'';

	exec('pkill -f "php ' . __DIR__ . '/bin/spotcommander-remote"');
	exec('pkill -f "/bin/bash ' . __DIR__ . '/bin/spotcommander-inotifywait ' . $inotifywait_file . '"');
	exec('pkill -f "/bin/bash ' . __DIR__ . '/bin/spotcommander-queue"');
	exec('pkill -f "inotifywait -e modify ' . $inotifywait_file . '"');
	exec('pkill -f "dbus-monitor --profile ' . $dbus_monitor_watch_expressions . '"');

	if(file_exists(daemon_socket)) unlink(daemon_socket);
}

function daemon_is_running()
{
	@$socket_connect = stream_socket_client('unix://' . daemon_socket, $errno, $errstr);

	if($socket_connect)
	{
		fwrite($socket_connect, json_encode(array('', '')) . "\n");
		$contents = stream_get_contents($socket_connect);
		fclose($socket_connect);
	}

	return $socket_connect;
}

function daemon_get_nowplaying()
{
	global $qdbus;

	$return = array();

	$return['playbackstatus'] = trim(shell_exec($qdbus . ' org.mpris.MediaPlayer2.spotify /org/mpris/MediaPlayer2 org.freedesktop.DBus.Properties.Get org.mpris.MediaPlayer2.Player PlaybackStatus'));

	if($return['playbackstatus'] == 'Playing' || $return['playbackstatus'] == 'Paused')
	{
		$metadata = shell_exec($qdbus . ' org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.GetMetadata');
		$lines = explode("\n", $metadata);

		foreach($lines as $line)
		{
			$line = trim($line);
			$line = explode(': ', $line, 2);

			if(!isset($line[1])) continue;

			$type = explode(':', $line[0]);
			$type = isset($type[1]) ? $type[1] : $type[0];

			$return[$type] = $line[1];
		}
	}

	return $return;
}

function daemon_inotifywait($action)
{
	file_write(__DIR__ . '/run/daemon.inotify', $action);
}

function daemon_user()
{
	return trim(file_get_contents(__DIR__ . '/run/daemon.user'));
}

function daemon_get_spotify_user_path()
{
	$path = trim(getenv('HOME')) . '/.config/spotify/Users';
	$user = (file_exists($path)) ? trim(shell_exec('ls -t "' . $path . '" | head -n 1')) : 'unknown-user';
	return $path . '/' . $user . '/';
}

function daemon_qdbus_select()
{
	$commands = array('/usr/lib/i386-linux-gnu/qt4/bin/qdbus', '/usr/lib/x86_64-linux-gnu/qt4/bin/qdbus', 'qdbus-qt4', 'qdbus');

	foreach($commands as $command)
	{
		if(trim(shell_exec('command -v ' . $command . ' 1>/dev/null 2>&1 && echo 1')) == 1) return $command;
	}

	return false;
}

function daemon_pulseaudio_check()
{
	return (trim(shell_exec('command -v pacmd 1>/dev/null 2>&1 && echo 1')) == 1);
}

// Remote control

function remote_control($action, $data)
{
	if(spotify_is_running() || $action == 'spotify_launch' || $action == 'get_spotify_guistate' || $action == 'get_spotify_username')
	{
		@$socket_connect = stream_socket_client('unix://' . daemon_socket, $errno, $errstr);

		if($socket_connect)
		{
			fwrite($socket_connect, json_encode(array($action, $data)) . "\n");
			$contents = stream_get_contents($socket_connect);
			fclose($socket_connect);

			return json_decode($contents, true);
		}
	}
}

function get_volume_control()
{
	return (empty($_COOKIE['settings_volume_control'])) ? 'spotify' : $_COOKIE['settings_volume_control'];
}

function get_current_volume()
{
	return intval(remote_control('get_current_volume', get_volume_control()));
}

function set_volume_before_mute($volume)
{
	file_write(__DIR__ . '/run/volume.save', $volume);
}

function get_volume_before_mute()
{
	return intval(trim(file_get_contents('run/volume.save')));
}

// Now playing

function get_nowplaying()
{
	return remote_control('get_nowplaying', '');
}

function track_is_playing($uri, $div)
{
	if($div == 'icon')
	{
		return (!empty($_COOKIE['nowplaying_uri']) && rawurldecode($_COOKIE['nowplaying_uri']) == $uri) ? 'playing_24_img_div' : 'track_24_img_div';
	}
	elseif($div == 'text')
	{
		return (!empty($_COOKIE['nowplaying_uri']) && rawurldecode($_COOKIE['nowplaying_uri']) == $uri) ? 'bold_text' : '';
	}
}

// Cover art

function get_cover_art($uri)
{
	$cover_art = cover_art_exists($uri);
	$file = 'cache/cover-art/' . md5($uri) . '.jpg';

	if(!$cover_art)
	{
		if(get_uri_type($uri) == 'playlist' || get_uri_type($uri) == 'artist')
		{
			$files = get_external_files(array(uri_to_url($uri)));
			preg_match('/property="og:image" content="(.*?)"/', $files[0], $cover);
		}
		else
		{
			$files = get_external_files(array('https://embed.spotify.com/?uri=' . $uri));
			preg_match('/data-ca="(.*?)"/', $files[0], $cover);
		}

		if(empty($cover[1]) || get_uri_type($cover[1]) != 'cover_art')
		{
			$cover = 'error';
		}
		else
		{
			$cover = $cover[1];
			$cover = str_replace(array('/thumb/', '/image/', '/120/', '/160/', '/300/', '/640/'), '/640/', $cover);

			$files = get_external_files(array($cover));
			if(!is_numeric($files[0])) file_put_contents($file, $files[0]);
		}
	}

	return (!$cover_art) ? $cover : $file;
}

function cover_art_exists($uri)
{
	$file = 'cache/cover-art/' . md5($uri) . '.jpg';
	return (file_exists($file)) ? $file : false;
}

// Recently played

function save_recently_played($artist, $title, $uri)
{
	$count = get_db_count('recently-played', "SELECT COUNT(id) as count FROM recently_played WHERE uri = '" . sqlite_escape($uri) . "'");
	if($count > 0) db_exec('recently-played', "DELETE FROM recently_played WHERE uri = '" . sqlite_escape($uri) . "'");

	$count = get_db_count('recently-played', "SELECT COUNT(id) as count FROM recently_played");
	if($count >= 10) db_exec('recently-played', "DELETE FROM recently_played WHERE id = (SELECT id FROM recently_played ORDER BY id LIMIT 1)");

	db_exec('recently-played', "INSERT INTO recently_played (artist, title, uri) VALUES ('" . sqlite_escape($artist) . "', '" . sqlite_escape($title) . "', '" . sqlite_escape($uri) . "')");
}

function clear_recently_played()
{
	db_exec('recently-played', "DELETE FROM recently_played");
}

// Queue

function queue_uri($artist, $title, $uri)
{
	if(!spotify_is_running()) return 'spotify_is_not_running';

	$count = get_db_count('queue', "SELECT COUNT(id) as count FROM queue");
	$sortorder = $count + 1;

	db_exec('queue', "INSERT INTO queue (artist, title, uri, sortorder) VALUES ('" . sqlite_escape($artist) . "', '" . sqlite_escape($title) . "', '" . sqlite_escape($uri) . "', '" . sqlite_escape($sortorder) . "')");
}

function queue_uris($uris, $randomly)
{
	if(!spotify_is_running()) return 'spotify_is_not_running';

	$type = get_uri_type($uris);
	$randomly = string_to_boolean($randomly);

	if($type == 'playlist')
	{
		$playlist = get_playlist($uris);

		if(empty($playlist)) return 'error';

		$tracks = $playlist['tracks'];

		if($randomly) shuffle($tracks);

		$i = 0;

		foreach($tracks as $track)
		{
			queue_uri($track['artist'], $track['title'], $track['uri']);

			$i++;
		}

		return $i;
	}
	elseif($type == 'album')
	{
		$album = lookup_album($uris);

		if(empty($album)) return 'error';

		$discs = $album['discs'];

		$i = 0;

		foreach($discs as $disc)
		{
			$tracks = $disc;

			if($randomly) shuffle($tracks);

			foreach($tracks as $track)
			{
				queue_uri($track['artist'], $track['title'], $track['uri']);

				$i++;
			}		
		}

		return $i;
	}
	else
	{
		$tracks = json_decode(base64_decode($uris), true);

		if(empty($tracks[0]['uri'])) return 'error';

		if($randomly) shuffle($tracks);

		$i = 0;

		foreach($tracks as $track)
		{
			queue_uri($track['artist'], $track['title'], $track['uri']);

			$i++;
		}

		return $i;
	}

	return 'error';
}

function move_queued_uri($id, $sortorder, $direction)
{
	$sortorder = intval($sortorder);
	$count = get_db_count('queue', "SELECT COUNT(id) as count FROM queue");

	if($direction == 'up' && $sortorder != 1)
	{
		$sortorder = $sortorder - 1;

		db_exec('queue', "UPDATE queue SET sortorder = sortorder + 1 WHERE sortorder = " . $sortorder);
		db_exec('queue', "UPDATE queue SET sortorder = sortorder - 1 WHERE id = '" . sqlite_escape($id) . "'");
	}
	elseif($direction == 'down' && $sortorder != $count)
	{
		$sortorder = $sortorder + 1;

		db_exec('queue', "UPDATE queue SET sortorder = sortorder - 1 WHERE sortorder = " . $sortorder);
		db_exec('queue', "UPDATE queue SET sortorder = sortorder + 1 WHERE id = '" . sqlite_escape($id) . "'");
	}
	elseif($direction == 'top' && $sortorder != 1)
	{
		db_exec('queue', "UPDATE queue SET sortorder = sortorder + 1 WHERE sortorder < " . $sortorder);
		db_exec('queue', "UPDATE queue SET sortorder = 1 WHERE id = '" . sqlite_escape($id) . "'");
	}
}

function remove_from_queue($id, $sortorder)
{
	$sortorder = intval($sortorder);

	db_exec('queue', "DELETE FROM queue WHERE id = '" . sqlite_escape($id) . "'");
	db_exec('queue', "UPDATE queue SET sortorder = sortorder - 1 WHERE sortorder > " . $sortorder);
}

function clear_queue()
{
	db_exec('queue', "DELETE FROM queue");
}

function queue_is_empty()
{
	$count = get_db_count('queue', "SELECT COUNT(id) as count FROM queue");
	return ($count == 0);
}

// Playlists

function get_playlist($playlist_uri)
{
	$return = null;
	$count = get_db_count('playlists-cache', "SELECT COUNT(id) as count FROM playlists WHERE uri = '" . sqlite_escape($playlist_uri) . "'");

	if($count == 1)
	{
		$rows = get_db_rows('playlists-cache', "SELECT metadata, time FROM playlists WHERE uri = '" . sqlite_escape($playlist_uri) . "'", array('metadata', 'time'));

		$current_time = time();
		$cache_time = intval($rows[1]['time']);
		$cache_expire = 900;

		if($current_time - $cache_time < $cache_expire)
		{
			$return = json_decode(base64_decode($rows[1]['metadata']), true);
		}
		else
		{
			$cover_art = cover_art_exists($playlist_uri);
			if($cover_art != false) unlink($cover_art);
			db_exec('playlists-cache', "DELETE FROM playlists WHERE uri = '" . sqlite_escape($playlist_uri) . "'");
		}
	}

	if(empty($return))
	{
		$files = get_external_files(array('https://embed.spotify.com/?uri=' . $playlist_uri));
		$playlist = $files[0];

		preg_match('/<div class="title-content ellipsis">(.*?)<\/div>/', $playlist, $name);
		preg_match_all('/<li class="artist .*>(.*?)<\/li>/', $playlist, $artists);
		preg_match_all('/<li class="track-title .*>(.*?)<\/li>/', $playlist, $titles);
		preg_match_all('/data-duration-ms="(.*?)"/', $playlist, $lengths);
		preg_match_all('/rel="track" data-track="(.*?)"/', $playlist, $uris);

		if(!empty($name[1]) && !empty($artists[1]) && !empty($titles[1]) && !empty($uris[1]) && !empty($lengths[1]))
		{
			$return = array();

			$return['name'] = hscd($name[1]);

			$user = explode(':', $playlist_uri);
			$user = is_facebook_user(urldecode($user[2]));

			$return['user'] = $user;

			$total_length = 0;
			$count = count($uris[1]);

			for($i = 0; $i < $count; $i++)
			{
				$return['tracks'][$i]['artist'] = hscd($artists[1][$i]);
				$return['tracks'][$i]['title'] = hscd(preg_replace('/^\d+\. /', '', $titles[1][$i]));
				$return['tracks'][$i]['length'] = convert_length($lengths[1][$i], 'ms');
				$return['tracks'][$i]['uri'] = 'spotify:track:' . $uris[1][$i];

				$total_length = $total_length + floatval($lengths[1][$i]);
			}

			$return['tracks_count'] = $count;
			$return['total_length'] = convert_length($total_length, 'ms');

			$count = get_db_count('playlists-cache', "SELECT COUNT(id) as count FROM playlists WHERE uri = '" . sqlite_escape($playlist_uri) . "'");
			if($count == 0 && get_uri_type($playlist_uri) != 'starred') db_exec('playlists-cache', "INSERT INTO playlists (uri, metadata, time) VALUES ('" . sqlite_escape($playlist_uri) . "', '" . sqlite_escape(base64_encode(json_encode($return))) . "', '" . time() . "')");
		}
	}

	return $return;
}

function add_playlists($uris)
{
	if(is_string($uris)) $uris = explode(' ', $uris);

	$get_uris = array();
	$i = 0;

	foreach($uris as $uri)
	{
		$uri = url_to_uri($uri);

		if(get_uri_type($uri) == 'playlist' && !playlist_is_saved($uri))
		{
			$get_uris[$i] = $uri;
			$get_urls[$i] = 'https://embed.spotify.com/?uri=' . $uri;

			$i++;
		}
	}

	$return = '';

	if(!empty($get_uris))
	{
		$files = get_external_files($get_urls);
		$playlists = $files;

		$i = 0;

		foreach($playlists as $playlist)
		{
			$uri = $get_uris[$i];
			preg_match('/<div class="title-content ellipsis">(.*?)<\/div>/', $playlist, $name);

			if(empty($name[1]))
			{
				$return = 'error';
			}
			else
			{
				$name = hscd($name[1]);
				if(!playlist_is_saved($uri)) db_exec('playlists', "INSERT INTO playlists (name, uri) VALUES ('" . sqlite_escape($name) . "', '" . sqlite_escape($uri) . "')");
			}

			$i++;
		}
	}

	return $return;
}

function add_spotify_playlists()
{
	$guistate = remote_control('get_spotify_guistate', '');

	if(empty($guistate['views'])) return 'error';

	$playlists = $guistate['views'];

	$uris = array();
	$i = 0;

	foreach($playlists as $playlist)
	{
		if(!empty($playlist['uri']) && get_uri_type($playlist['uri']) == 'playlist')
		{
			$uris[$i] = $playlist['uri'];

			$i++;
		}
	}

	return add_playlists($uris);
}

function playlist_is_saved($uri)
{
	$count = get_db_count('playlists', "SELECT COUNT(id) as count FROM playlists WHERE uri = '" . sqlite_escape($uri) . "'");
	return ($count != 0);
}

function remove_playlist($id)
{
	db_exec('playlists', "DELETE FROM playlists WHERE id = '" . sqlite_escape($id) . "'");
}

function is_facebook_user($user)
{
	return (is_numeric($user)) ? 'Facebook user: ' . $user : $user;
}

// Starred

function star_uri($type, $artist, $title, $uri)
{
	$count = get_db_count('starred', "SELECT COUNT(id) as count FROM starred WHERE uri = '" . sqlite_escape($uri) . "'");
	if($count == 0) db_exec('starred', "INSERT INTO starred (type, artist, title, uri) VALUES ('" . sqlite_escape($type) . "', '" . sqlite_escape($artist) . "', '" . sqlite_escape($title) . "', '" . sqlite_escape($uri) . "')");
}

function unstar_uri($uri)
{
	db_exec('starred', "DELETE FROM starred WHERE uri = '" . sqlite_escape($uri) . "'");
}

function uri_is_starred($uri)
{
	$count = get_db_count('starred', "SELECT COUNT(id) as count FROM starred WHERE uri = '" . sqlite_escape($uri) . "'");
	return ($count == 0) ? 'star' : 'unstar';
}

function import_starred_tracks($uris)
{
	if(is_string($uris)) $uris = explode(' ', $uris);

	$lookup_uris = array();
	$i = 0;

	foreach($uris as $uri)
	{
		$uri = url_to_uri($uri);
		$type = get_uri_type($uri);

		if(uri_is_starred($uri) == 'star')
		{
			if($type == 'track')
			{
				$lookup_uris[$i] = $uri;

				$i++;
			}
			elseif($type == 'local')
			{
				$track = explode(':', $uri);

				$artist = sqlite_escape(urldecode($track[2]));
				$title = sqlite_escape(urldecode($track[4]));

				star_uri('track', $artist, $title, $uri);
			}
		}
	}

	$return = '';

	if(!empty($lookup_uris))
	{
		$tracks = lookup_tracks($lookup_uris);

		foreach($tracks as $track)
		{
			if(empty($track))
			{
				$return = 'error';
			}
			else
			{
				$artist = $track['artists'][0]['name'];
				$title = $track['name'];
				$uri = $track['uri'];

				star_uri('track', $artist, $title, $uri);
			}
		}
	}

	return $return;
}

function import_starred_spotify_tracks()
{
	$user = get_spotify_username();

	$playlist = get_playlist('spotify:user:' . $user . ':starred');

	if(empty($playlist)) return 'error';

	$tracks = $playlist['tracks'];

	$uris = array();
	$i = 0;

	foreach($tracks as $track)
	{
		if(get_uri_type($track['uri']) == 'track')
		{	
			$uris[$i] = $track['uri'];

			$i++;
		}
	}

	return import_starred_tracks($uris);
}

// Discover

function get_chart($chart, $country_code)
{
	$return = null;

	$files = get_external_files(array('http://charts.spotify.com/embed/charts/most_' . $chart . '/' . strtolower($country_code) . '/latest'));
	$chart = $files[0];

	preg_match_all('/<span class="track-artist">(.*?)<\/span>/s', $chart, $artists);
	preg_match_all('/<span class="track-name">(.*?)<\/span>/s', $chart, $titles);
	preg_match_all('/<a href="https:\/\/play.spotify.com\/track\/(.*?)"/s', $chart, $uris);
	preg_match_all('/<span class="track-stats">(.*?)<\/span>/s', $chart, $plays);

	if(!empty($artists[1]) && !empty($titles[1]) && !empty($uris[1]) && !empty($plays[1]))
	{
		$return = array();
		$count = count($uris[1]);

		for($i = 0; $i < $count; $i++)
		{
			$return[$i]['artist'] = hscd(trim($artists[1][$i]));
			$return[$i]['title'] = hscd(trim($titles[1][$i]));
			$return[$i]['uri'] = 'spotify:track:' . $uris[1][$i];
			$return[$i]['plays'] = trim(str_replace('plays', '', $plays[1][$i]));
		}
	}

	return $return;
}

// Search & lookup

function search_spotify($search, $string)
{
	$return = null;
	$count = get_db_count('search-cache', "SELECT COUNT(id) as count FROM strings WHERE string = '" . sqlite_escape($string) . "' COLLATE NOCASE");

	if($count == 1)
	{
		$rows = get_db_rows('search-cache', "SELECT metadata, time FROM strings WHERE string = '" . sqlite_escape($string) . "' COLLATE NOCASE", array('metadata', 'time'));

		$current_time = time();
		$cache_time = intval($rows[1]['time']);
		$cache_expire = 86400;

		if($current_time - $cache_time < $cache_expire)
		{
			$return = json_decode(base64_decode($rows[1]['metadata']), true);
		}
		else
		{
			db_exec('search-cache', "DELETE FROM strings WHERE string = '" . sqlite_escape($string) . "' COLLATE NOCASE");
		}
	}

	if(empty($return))
	{
		if($search == 'tracks')
		{
			$urls = array('https://api.spotify.com/v1/search?type=track&limit=50&q=' . urlencode($string));	
		}
		elseif($search == 'albums')
		{
			$urls = array('https://api.spotify.com/v1/search?type=album&limit=20&q=' . urlencode($string));
		}
		elseif($search == 'all')
		{
			$urls = array('https://api.spotify.com/v1/search?type=track&limit=50&q=' . urlencode($string), 'https://api.spotify.com/v1/search?type=album&limit=20&q=' . urlencode($string));
		}

		$files = get_external_files($urls);

		if($search == 'tracks')
		{
			$tracks = json_decode($files[0], true);
			$albums = null;
		}
		elseif($search == 'albums')
		{
			$tracks = null;
			$albums = json_decode($files[0], true);
		}
		elseif($search == 'all')
		{
			$tracks = json_decode($files[0], true);
			$albums = json_decode($files[1], true);
		}

		if($search == 'tracks' && isset($tracks['tracks']) || $search == 'albums' && isset($albums['albums']) || $search == 'all' && isset($tracks['tracks']) && isset($albums['albums']))
		{
			$return = array();
			$return['tracks'] = (empty($tracks)) ? null : array();
			$return['albums'] = (empty($albums)) ? null : array();

			$tracks = $tracks['tracks']['items'];
			$albums = $albums['albums']['items'];

			if(!empty($tracks))
			{
				$i = 0;

				foreach($tracks as $track)
				{
					$return['tracks'][$i]['artist'] = get_artists($track['artists']);
					$return['tracks'][$i]['artist_uri'] = (empty($track['artists'][0]['uri'])) ? '' : $track['artists'][0]['uri'];
					$return['tracks'][$i]['title'] = $track['name'];
					$return['tracks'][$i]['length'] = convert_length($track['duration_ms'], 'ms');
					$return['tracks'][$i]['popularity'] = $track['popularity'] . ' %';
					$return['tracks'][$i]['uri'] = $track['uri'];
					$return['tracks'][$i]['album'] = $track['album']['name'];
					$return['tracks'][$i]['album_uri'] = $track['album']['uri'];
					$return['tracks'][$i]['album_regions'] = $track['available_markets'];

					$i++;
				}
			}

			if(!empty($albums))
			{
				$album_ids = array();
				$i = 0;

				foreach($albums as $album)
				{
					$album_ids[$i] = uri_to_id($album['uri']);

					$i++;
				}

				$album_ids = implode(',', $album_ids);

				$files = get_external_files(array('https://api.spotify.com/v1/albums?ids=' . $album_ids));
				$albums = json_decode($files[0], true);

				if(empty($albums['albums']))
				{
					$return = null;
				}
				else
				{
					$albums = $albums['albums'];
					$i = 0;

					foreach($albums as $album)
					{
						$return['albums'][$i]['artist'] = get_artists($album['artists']);
						$return['albums'][$i]['artist_uri'] = (empty($album['artists'][0]['uri'])) ? '' : $album['artists'][0]['uri'];
						$return['albums'][$i]['title'] = $album['name'];
						$return['albums'][$i]['type'] = ucfirst($album['album_type']);
						$return['albums'][$i]['released'] = $album['release_date'];
						$return['albums'][$i]['popularity'] = $album['popularity'] . ' %';
						$return['albums'][$i]['uri'] = $album['uri'];

						$i++;
					}
				}
			}

			$count = get_db_count('search-cache', "SELECT COUNT(id) as count FROM strings WHERE string = '" . sqlite_escape($string) . "' COLLATE NOCASE");
			if($count == 0) db_exec('search-cache', "INSERT INTO strings (string, metadata, time) VALUES ('" . sqlite_escape($string) . "', '" . sqlite_escape(base64_encode(json_encode($return))) . "', '" . time() . "')");
		}
	}

	return $return;
}

function lookup_tracks($uris)
{
	$urls = array();
	$i = 0;

	foreach($uris as $uri)
	{
		$urls[$i] = 'https://api.spotify.com/v1/tracks/' . uri_to_id($uri);

		$i++;
	}

	$files = get_external_files($urls);
	$tracks = $files;

	$return = array();
	$i = 0;

	foreach($tracks as $track)
	{
		$track = json_decode($track, true);	
		$return[$i] = (empty($track['name']) || empty($track['uri'])) ? null : $track;

		$i++;
	}

	return $return;
}

function lookup_album($uri)
{
	$return = null;
	$count = get_db_count('albums-cache', "SELECT COUNT(id) as count FROM albums WHERE uri = '" . sqlite_escape($uri) . "'");

	if($count == 1)
	{
		$rows = get_db_rows('albums-cache', "SELECT metadata FROM albums WHERE uri = '" . sqlite_escape($uri) . "'", array('metadata'));
		$return = json_decode(base64_decode($rows[1]['metadata']), true);
	}
	else
	{
		$files = get_external_files(array('https://api.spotify.com/v1/albums/' . uri_to_id($uri)));
		$album = json_decode($files[0], true);

		if(!empty($album['name']) && !empty($album['tracks']['items']))
		{
			$return = array();
			$return['artist'] = get_artists($album['artists']);
			$return['artist_uri'] = (empty($album['artists'][0]['id'])) ? '' : $album['artists'][0]['uri'];
			$return['title'] = $album['name'];
			$return['released'] = (empty($album['release_date'])) ? 'Unknown' : $album['release_date'];
			$return['popularity'] = $album['popularity'] . ' %';
			$return['cover_art_uri'] = $album['images'][0]['url'];
			$return['cover_art_width'] = $album['images'][0]['width'];
			$return['cover_art_height'] = $album['images'][0]['height'];
			$return['regions'] = $album['available_markets'];

			$tracks = $album['tracks']['items'];

			$total_length = 0;
			$i = 0;

			foreach($tracks as $track)
			{
				$disc_number = $track['disc_number'];

				$return['discs'][$disc_number][$i]['artist'] = get_artists($track['artists']);
				$return['discs'][$disc_number][$i]['artist_uri'] = $track['artists'][0]['uri'];
				$return['discs'][$disc_number][$i]['title'] = $track['name'];
				$return['discs'][$disc_number][$i]['track_number'] = $track['track_number'];
				$return['discs'][$disc_number][$i]['disc_number'] = $disc_number;
				$return['discs'][$disc_number][$i]['length'] = convert_length($track['duration_ms'], 'ms');
				$return['discs'][$disc_number][$i]['uri'] = $track['uri'];

				$total_length = $total_length + floatval($track['duration_ms']);

				$i++;
			}

			$return['tracks_count'] = count($tracks);
			$return['total_length'] = convert_length($total_length, 'ms');

			$count = get_db_count('albums-cache', "SELECT COUNT(id) as count FROM albums WHERE uri = '" . sqlite_escape($uri) . "'");
			if($count == 0) db_exec('albums-cache', "INSERT INTO albums (uri, metadata) VALUES ('" . sqlite_escape($uri) . "', '" . sqlite_escape(base64_encode(json_encode($return))) . "')");
		}
	}

	return $return;
}

function lookup_artist($uri, $region)
{
	$region = ($region == 'ALL') ? 'US' : $region;

	$return = null;
	$count = get_db_count('artists-cache', "SELECT COUNT(id) as count FROM artists WHERE uri = '" . sqlite_escape($uri) . "'");

	if($count == 1)
	{
		$rows = get_db_rows('artists-cache', "SELECT metadata, time FROM artists WHERE uri = '" . sqlite_escape($uri) . "'", array('metadata', 'time'));

		$current_time = time();
		$cache_time = intval($rows[1]['time']);
		$cache_expire = 86400;

		if($current_time - $cache_time < $cache_expire)
		{
			$return = json_decode(base64_decode($rows[1]['metadata']), true);
		}
		else
		{
			db_exec('artists-cache', "DELETE FROM artists WHERE uri = '" . sqlite_escape($uri) . "'");
		}
	}

	if(empty($return))
	{
		$files = get_external_files(array('https://api.spotify.com/v1/artists/' . uri_to_id($uri), 'https://api.spotify.com/v1/artists/' . uri_to_id($uri) . '/top-tracks?country=' . $region, 'https://api.spotify.com/v1/artists/' . uri_to_id($uri) . '/albums?limit=20'));

		$artist_metadata = json_decode($files[0], true);
		$artist_tracks = json_decode($files[1], true);
		$artist_albums = json_decode($files[2], true);

		if(!empty($artist_metadata['uri']) && !empty($artist_tracks['tracks']) && !empty($artist_albums['items']))
		{
			$return = array();
			$return['artist'] = $artist_metadata['name'];
			$return['popularity'] = $artist_metadata['popularity'] . ' %';
			$return['cover_art_uri'] = $artist_metadata['images'][0]['url'];
			$return['cover_art_width'] = $artist_metadata['images'][0]['width'];
			$return['cover_art_height'] = $artist_metadata['images'][0]['height'];
			$return['uri'] = $artist_metadata['uri'];

			$albums = $artist_albums['items'];
			$tracks = $artist_tracks['tracks'];

			$i = 0;

			foreach($tracks as $track)
			{
				$return['tracks'][$i]['artist'] = get_artists($track['artists']);
				$return['tracks'][$i]['title'] = $track['name'];
				$return['tracks'][$i]['length'] = convert_length($track['duration_ms'], 'ms');
				$return['tracks'][$i]['popularity'] = $track['popularity'] . ' %';
				$return['tracks'][$i]['uri'] = $track['uri'];
				$return['tracks'][$i]['album'] = $track['album']['name'];
				$return['tracks'][$i]['album_uri'] = $track['album']['uri'];

				$i++;
			}

			$album_ids = array();
			$i = 0;

			foreach($albums as $album)
			{
				$album_ids[$i] = uri_to_id($album['uri']);

				$i++;
			}

			$album_ids = implode(',', $album_ids);

			$files = get_external_files(array('https://api.spotify.com/v1/albums?ids=' . $album_ids));
			$albums = json_decode($files[0], true);

			if(empty($albums['albums']))
			{
				$return = null;
			}
			else
			{
				$albums = $albums['albums'];
				$i = 0;

				foreach($albums as $album)
				{
					$return['albums'][$i]['artist'] = get_artists($album['artists']);
					$return['albums'][$i]['title'] = $album['name'];
					$return['albums'][$i]['type'] = ucfirst($album['album_type']);
					$return['albums'][$i]['released'] = $album['release_date'];
					$return['albums'][$i]['popularity'] = $album['popularity'] . ' %';
					$return['albums'][$i]['uri'] = $album['uri'];

					$i++;
				}

				$return['albums_count'] = count($return['albums']);
			}

			$count = get_db_count('artists-cache', "SELECT COUNT(id) as count FROM artists WHERE uri = '" . sqlite_escape($uri) . "'");
			if($count == 0) db_exec('artists-cache', "INSERT INTO artists (uri, metadata, time) VALUES ('" . sqlite_escape($uri) . "', '" . sqlite_escape(base64_encode(json_encode($return))) . "', '" . time() . "')");
		}
	}

	return $return;
}

function lookup_track_album($uri)
{
	$count = get_db_count('track-album-cache', "SELECT COUNT(id) as count FROM tracks WHERE track_uri = '" . sqlite_escape($uri) . "'");

	if($count == 1)
	{
		$rows = get_db_rows('track-album-cache', "SELECT album_uri FROM tracks WHERE track_uri = '" . sqlite_escape($uri) . "'", array('album_uri'));
		$return = $rows[1]['album_uri'];
	}
	else
	{
		$tracks = lookup_tracks(array($uri));
		$return = (empty($tracks[0]['album']['uri'])) ? null : $tracks[0]['album']['uri'];
	}

	return $return;
}

function lookup_track_artist($uri)
{
	$count = get_db_count('track-artist-cache', "SELECT COUNT(id) as count FROM tracks WHERE track_uri = '" . sqlite_escape($uri) . "'");

	if($count == 1)
	{
		$rows = get_db_rows('track-artist-cache', "SELECT artist_uri FROM tracks WHERE track_uri = '" . sqlite_escape($uri) . "'", array('artist_uri'));
		$return = $rows[1]['artist_uri'];
	}
	else
	{
		$tracks = lookup_tracks(array($uri));
		$return = (empty($tracks[0]['artists'][0]['uri'])) ? null : $tracks[0]['artists'][0]['uri'];
	}

	return $return;
}

function lookup_album_artist($uri)
{
	$album = lookup_album($uri);
	$return = (empty($album['artist_uri'])) ? null : $album['artist_uri'];

	return $return;
}

function is_available_in_region($region, $regions)
{
	return ($region == 'ALL' || in_array($region, $regions, true));
}

function save_track_album($track_uri, $album_uri)
{
	$count = get_db_count('track-album-cache', "SELECT COUNT(id) as count FROM tracks WHERE track_uri = '" . sqlite_escape($track_uri) . "'");
	if($count == 0) db_exec('track-album-cache', "INSERT INTO tracks (track_uri, album_uri) VALUES ('" . sqlite_escape($track_uri) . "', '" . sqlite_escape($album_uri) . "')");	
}

function save_track_artist($track_uri, $artist_uri)
{
	$count = get_db_count('track-artist-cache', "SELECT COUNT(id) as count FROM tracks WHERE track_uri = '" . sqlite_escape($track_uri) . "'");
	if($count == 0) db_exec('track-artist-cache', "INSERT INTO tracks (track_uri, artist_uri) VALUES ('" . sqlite_escape($track_uri) . "', '" . sqlite_escape($artist_uri) . "')");	
}

function get_artists($artists)
{
	$return = '';

	foreach($artists as $artist)
	{
		$return .= $artist['name'] . ', ';
	}

	return rtrim($return, ', ');
}

function get_search_type($string)
{
	$type = 'unknown';

	if(preg_match('/^tag:(new|"new")$/', $string))
	{
		$type = 'tag_new';
	}
	elseif(preg_match('/track:/', $string))
	{
		$type = 'track';
	}
	elseif(preg_match('/isrc:/', $string))
	{
		$type = 'isrc';
	}
	elseif(preg_match('/upc:/', $string))
	{
		$type = 'upc';
	}

	return $type;
}

function get_search_title($string)
{
	if(preg_match('/^(artist|track|album|year|genre|label|isrc|upc|tag):[^"][^ ]+[^"]$/', $string) || preg_match('/^(artist|track|album|genre|label|isrc|upc|tag):"[^"]+"$/', $string))
	{
		$string = explode(':', $string, 2);
		$type = ($string[0] == 'isrc' || $string[0] == 'upc') ? strtoupper($string[0]) : ucfirst($string[0]);
		$query =  ucfirst(trim($string[1], '"'));

		$title = $type . ': ' . $query;
	}
	elseif(preg_match('/^(artist|track|album|year|genre|label|isrc|upc|tag):/', $string))
	{
		$title = $string;
	}
	else
	{
		$title = ucfirst($string);
	}

	return $title;
}

function save_search_history($string)
{
	$count = get_db_count('search-history', "SELECT COUNT(id) as count FROM search_history WHERE string = '" . sqlite_escape($string) . "' COLLATE NOCASE");
	if($count > 0) db_exec('search-history', "DELETE FROM search_history WHERE string = '" . sqlite_escape($string) . "' COLLATE NOCASE");

	$count = get_db_count('search-history', "SELECT COUNT(id) as count FROM search_history");
	if($count >= 10) db_exec('search-history', "DELETE FROM search_history WHERE id = (SELECT id FROM search_history ORDER BY id LIMIT 1)");

	db_exec('search-history', "INSERT INTO search_history (string) VALUES ('" . sqlite_escape($string) . "')");
}

function clear_search_history()
{
	db_exec('search-history', "DELETE FROM search_history");
}

// Settings

function get_setting_dropdown($setting, $options)
{
	$return = '<select class="setting_select" name="' . $setting . '">';

	foreach($options as $option_value => $option_name)
	{
		$return .= '<option value="' . $option_value . '"' . setting_dropdown_status($setting, $option_value) . '>' . $option_name . '</option>';
	}

	$return .= '</select>';

	return $return;
}

function setting_checkbox_status($cookie)
{
	if(isset($_COOKIE[$cookie]) && $_COOKIE[$cookie] == 'true') return ' checked="checked"';
}

function setting_dropdown_status($cookie, $value)
{
	if(isset($_COOKIE[$cookie]) && $_COOKIE[$cookie] == $value) return ' selected="selected"';
}

// Clear cache

function clear_cache()
{
	db_exec('albums-cache', "DELETE FROM albums");
	db_exec('artists-cache', "DELETE FROM artists");
	db_exec('lyrics-cache', "DELETE FROM lyrics");
	db_exec('playlists-cache', "DELETE FROM playlists");
	db_exec('search-cache', "DELETE FROM strings");
	db_exec('track-album-cache', "DELETE FROM tracks");
	db_exec('track-artist-cache', "DELETE FROM tracks");

	delete_dir_files('cache/cover-art/');
}

// Files

function file_write($file, $content)
{
	if(file_exists($file))
	{
		$fwrite = fopen($file, 'w');
		fwrite($fwrite, $content);
		fclose($fwrite);
	}
}

function delete_dir_files($dir)
{
	$files = glob($dir . '*');

	foreach($files as $file)
	{
		if(file_exists($file) && is_file($file)) unlink($file);
	}
}

function get_external_files($uris)
{
	$count = count($uris);

	$mh = curl_multi_init();

	for($i = 0; $i < $count; $i++)
	{
		$uri = $uris[$i];
		$ua = (ssw($uri, 'http://open.spotify.com/')) ? 'Mozilla/5.0 (Android; Mobile; rv:30.0) Gecko/30.0 Firefox/30.0' : 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:30.0) Gecko/20100101 Firefox/30.0';

		$ch[$i] = curl_init();

		curl_setopt($ch[$i], CURLOPT_URL, $uri);
		curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch[$i], CURLOPT_TIMEOUT_MS, 10000);
		curl_setopt($ch[$i], CURLOPT_USERAGENT, $ua);

		if(config_proxy)
		{
			curl_setopt($ch[$i], CURLOPT_PROXY, config_proxy_address);
			curl_setopt($ch[$i], CURLOPT_PROXYPORT, config_proxy_port);
		}

		curl_multi_add_handle($mh, $ch[$i]);
	}

	$active = null;

	do
	{
		$mrc = curl_multi_exec($mh, $active);
	}
	while($mrc == CURLM_CALL_MULTI_PERFORM);

	while($active && $mrc == CURLM_OK)
	{
		if(curl_multi_select($mh) === -1) usleep(100000);

		do
		{
			$mrc = curl_multi_exec($mh, $active);
		}
		while($mrc == CURLM_CALL_MULTI_PERFORM);
	}

	$return = array();

	for($i = 0; $i < $count; $i++)
	{
		$status = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);

		$return[$i] = ($status == 200) ? curl_multi_getcontent($ch[$i]) : $status;

		curl_multi_remove_handle($mh, $ch[$i]);
	}

	curl_multi_close($mh);

	return $return;
}

// Databases

function db_exec($db, $exec)
{
	$db = new SQLite3(__DIR__ . '/db/' . $db . '.db');
	@$db->exec($exec);
	$db->close();
}

function get_db_rows($db, $query, $columns)
{
	$db = new SQLite3(__DIR__ . '/db/' . $db . '.db');

	@$query = $db->query($query);

	if(!is_object($query)) return;

	$return = array();
	$w = 0;

	while($row = $query->fetchArray(SQLITE3_ASSOC))
	{
		$w++; 

		foreach($columns as $column)
		{
			$return[$w][$column] = $row[$column];
		}
	}

	$db->close();

	return $return;
}

function get_db_count($db, $query)
{
	$db = new SQLite3(__DIR__ . '/db/' . $db . '.db');

	@$query = $db->query($query);

	if(!is_object($query)) return 0;

	$return = $query->fetchArray(SQLITE3_ASSOC);

	$db->close();

	return intval($return['count']);
}

// Check stuff

function check_for_errors()
{
	$code = 0;

	if(!defined('config_send_system_information') || !defined('config_proxy') || !defined('config_proxy_address') || !defined('config_proxy_port') || !is_bool(config_send_system_information) || !is_bool(config_proxy) || !is_string(config_proxy_address) || !is_int(config_proxy_port))
	{
		$code = 1;
	}
	elseif(!daemon_is_running())
	{
		$code =  2;
	}
	elseif(!is_writeable('cache/cover-art') || !is_writeable('db/playlists.db') || !is_writeable('run/daemon.inotify'))
	{
		$code =  3;
	}
	elseif(!daemon_qdbus_select())
	{
		$code =  4;
	}

	return $code;
}

function check_for_updates()
{
	$sysinfo = get_system_information();

	$files = get_external_files(array(project_website . 'api/1/latest-version/?version=' . rawurlencode(number_format(project_version, 1)) . '&uname=' . rawurlencode($sysinfo['uname']) . '&ua=' . rawurlencode($sysinfo['ua'])));
	$latest_version = trim($files[0]);

	return (preg_match('/^\d+\.\d+$/', $latest_version)) ? $latest_version : 'error';
}

function get_system_information()
{
	$uname = trim(shell_exec('uname -mrsv'));
	$ua = (empty($_SERVER['HTTP_USER_AGENT'])) ? 'Unknown' : trim($_SERVER['HTTP_USER_AGENT']);

	$return = array();

	$return['uname'] = (defined('config_send_system_information') && config_send_system_information) ? $uname : 'Disabled';
	$return['ua'] = (defined('config_send_system_information') && config_send_system_information) ? $ua : 'Disabled';

	return $return;
}

function boolean_to_string($bool)
{
	return ($bool) ? 'true' : 'false';
}

function string_to_boolean($string)
{
	return ($string == 'true');
}

function ssw($string, $start)
{
	$start = str_replace(array('.', '*', '/'), array('\.', '\*', '\/'), $start);
	return (preg_match('/^' . $start . '/', $string));
}

function get_tracks_count($count)
{
	return ($count > 1) ? $count . ' tracks' : $count . ' track';
}

function is_sorted($cookie)
{
	return ($_COOKIE[$cookie] == 'default') ? 'sort' : 'sorted';
}

// Manipulate stuff

function convert_length($length, $from)
{
	$divide = 1;

	if($from == 'ms')
	{
		$divide = 1000;
	}
	elseif($from == 'mc')
	{
		$divide = 1000000;
	}

	$length = intval($length) / $divide;
	$minutes = $length / 60;

	if($minutes >= 60)
	{
		$hours = $minutes / 60;
		$minutes = ($hours - floor($hours)) * 60;
		$length =  floor($hours) . ' h ' . round($minutes) . ' m';
	}
	else
	{
		$seconds = sprintf('%02s', $length % 60);
		$length = floor($minutes) . ':' . $seconds;
	}

	return $length;
}

function convert_popularity($popularity)
{
	return round(floatval($popularity) * 100) . ' %';
}

function strip_string($string)
{
	return preg_replace('/[^a-zA-Z0-9-\s]/', '', $string);
}

function hsc($string)
{
	return htmlspecialchars($string, ENT_QUOTES);
}

function hscd($string)
{
	return htmlspecialchars_decode($string, ENT_QUOTES);
}

function sqlite_escape($string)
{
	return SQLite3::escapeString($string);
}

// URIs

function get_uri_type($uri)
{
	$type = 'unknown';

	if(preg_match('/^spotify:artist:\w{22}$/', $uri) || preg_match('/^http:\/\/open\.spotify\.com\/artist\/\w{22}$/', $uri))
	{
		$type = 'artist';
	}
	elseif(preg_match('/^spotify:track:\w{22}$/', $uri) || preg_match('/^http:\/\/open\.spotify\.com\/track\/\w{22}$/', $uri))
	{
		$type = 'track';
	}
	elseif(preg_match('/^spotify:local:[^:]+:[^:]*:[^:]+:\d+$/', $uri) || preg_match('/^http:\/\/open\.spotify\.com\/local\/[^\/]+\/[^\/]*\/[^\/]+\/\d+$/', $uri))
	{
		$type = 'local';
	}
	elseif(preg_match('/^spotify:album:\w{22}$/', $uri) || preg_match('/^http:\/\/open\.spotify\.com\/album\/\w{22}$/', $uri))
	{
		$type = 'album';
	}
	elseif(preg_match('/^spotify:user:[^:]+:playlist:\w{22}$/', $uri) || preg_match('/^http:\/\/open\.spotify\.com\/user\/[^\/]+\/playlist\/\w{22}$/', $uri))
	{
		$type = 'playlist';
	}
	elseif(preg_match('/^spotify:user:[^:]+:starred$/', $uri) || preg_match('/^http:\/\/open\.spotify\.com\/user\/[^\/]+\/starred$/', $uri))
	{
		$type = 'starred';
	}
	elseif(preg_match('/^http:\/\/o\.scdn\.co\/\w+\/\w+$/', $uri) || preg_match('/^https:\/\/\w+\.cloudfront\.net\/\w+\/\w+$/', $uri))
	{
		$type = 'cover_art';
	}

	return $type;
}

function uri_to_url($uri)
{
	if(ssw($uri, 'spotify:'))
	{
		$type = get_uri_type($uri);

		if($type == 'artist')
		{
			$uri = str_replace('spotify:artist:', 'http://open.spotify.com/artist/', $uri);
		}
		elseif($type == 'track')
		{
			$uri = str_replace('spotify:track:', 'http://open.spotify.com/track/', $uri);
		}
		elseif($type == 'local')
		{
			$uri = str_replace(array('spotify:local:', ':'), array('', '/'), $uri);
			$uri = 'http://open.spotify.com/local/' . $uri;
		}
		elseif($type == 'album')
		{
			$uri = str_replace('spotify:album:', 'http://open.spotify.com/album/', $uri);
		}
		elseif($type == 'playlist')
		{
			$uri = explode(':', $uri);
			$uri = 'http://open.spotify.com/user/' . $uri[2] . '/playlist/' . $uri[4];
		}
		elseif($type == 'starred')
		{
			$uri = explode(':', $uri);
			$uri = 'http://open.spotify.com/user/' . $uri[2] . '/starred';
		}
	}

	return $uri;
}

function url_to_uri($uri)
{
	if(ssw($uri, 'http://open.spotify.com/'))
	{
		$type = get_uri_type($uri);

		if($type == 'artist')
		{
			$uri = str_replace('http://open.spotify.com/artist/', '', $uri);
			$uri = 'spotify:track:' . $uri;
		}
		elseif($type == 'track')
		{
			$uri = str_replace('http://open.spotify.com/track/', '', $uri);
			$uri = 'spotify:track:' . $uri;
		}
		elseif($type == 'local')
		{
			$uri = str_replace(array('http://open.spotify.com/local/', '/'), array('', ':'), $uri);
			$uri = 'spotify:local:' . $uri;
		}
		elseif($type == 'album')
		{
			$uri = str_replace('http://open.spotify.com/album/', '', $uri);
			$uri = 'spotify:album:' . $uri;
		}
		elseif($type == 'playlist')
		{
			$uri = str_replace(array('http://open.spotify.com/user/', '/'), array('', ':'), $uri);
			$uri = 'spotify:user:' . $uri;
		}
		elseif($type == 'starred')
		{
			$uri = str_replace(array('http://open.spotify.com/user/', '/'), array('', ':'), $uri);
			$uri = 'spotify:user:' . $uri;
		}
	}

	return $uri;
}

function uri_to_id($uri)
{
	preg_match('/spotify:.+:(.*?)$/', $uri, $ids);
	return $ids[1];
}

// Native apps

function native_app_action($action)
{
	if(!daemon_is_running())
	{
		return 'Daemon is not running';
	}
	elseif(!spotify_is_running())
	{
		return 'Spotify is not running';
	}

	remote_control($action, '');

	if($action == 'next')
	{
		$usleep = (queue_is_empty()) ? 500000 : 1000000;

		usleep($usleep);

		$nowplaying = get_nowplaying();
		$playbackstatus = $nowplaying['playbackstatus'];

		return ($playbackstatus == 'Playing') ? $nowplaying['artist'] . ' - ' . $nowplaying['title'] : 'No music is playing';
	}

	return '';
}

?>