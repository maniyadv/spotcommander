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

$artist = explode(', ', rawurldecode($_GET['artist']));
$artist = $artist[0];

$title = rawurldecode($_GET['title']);

$query_artist = $artist;
$query_title = $title;

$replace = array(
	'/feat\..*/i',
	'/featuring.*/i',
	'/ con .*/i',
	'/ & .*/i'
);

$query_artist = preg_replace($replace, '', $query_artist);
$query_artist = str_replace(array('&', '$'), array('and', ''), $query_artist);
$query_artist = strip_string(trim($query_artist));
$query_artist = str_replace(array(' - ', ' '), '-', $query_artist);

$replace = array(
	'acoustic version',
	'new album version',
	'original album version',
	'album version',
	'bonus track',
	'clean version',
	'club mix',
	'demo version',
	'extended mix',
	'extended outro',
	'extended version',
	'extended',
	'explicit version',
	'explicit',
	'(live)',
	'- live',
	'live version',
	'lp mix',
	'(original)',
	'original edit',
	'original mix edit',
	'original version',
	'(radio)',
	'radio edit',
	'radio mix',
	'remastered version',
	're-mastered version',
	'remastered digital version',
	're-mastered digital version',
	'remastered',
	'remaster',
	'remixed version',
	'remix',
	'single version',
	'studio version',
	'version acustica',
	'versión acústica',
	'vocal edit'
);

$query_title = str_ireplace($replace, '', $query_title);

$replace = array(
	'/feat\..*/i',
	'/featuring.*/i',
	'/ con .*/i',
	'/featuring.*/i',
	'/- includes.*/i',
	'/\(includes.*/i',
	'/\(live at.*/i',
	'/- 19.*/i',
	'/- 20.*/i',
	'/\(19.*/i',
	'/\(20.*/i'
);

$query_title = preg_replace($replace, '', $query_title);
$query_title = str_replace(array('&', '$'), array('and', 's'), $query_title);
$query_title = strip_string(trim($query_title));
$query_title = str_replace(array(' - ', ' '), '-', $query_title);
$query_title = rtrim($query_title, '-');

$uri = strtolower('http://www.lyrics.com/' . $query_title .'-lyrics-' . $query_artist . '.html');

$error = false;
$no_match = false;

$count = get_db_count('lyrics-cache', "SELECT COUNT(id) as count FROM lyrics WHERE md5 = '" . md5($uri) . "'");

if($count == 1)
{
	$lyrics = get_db_rows('lyrics-cache', "SELECT lyrics FROM lyrics WHERE md5 = '" . md5($uri) . "'", array('lyrics'));
	$lyrics = base64_decode($lyrics[1]['lyrics']);
}
else
{
	$files = get_external_files(array($uri), null, null);
	$file = $files[0];

	preg_match('/<div id="lyric_space">(.*?)<\/div>/s', $file, $lyrics);

	$lyrics = (empty($lyrics[1])) ? null : $lyrics[1];

	if(empty($file))
	{
		$error = true;
	}
	elseif(empty($lyrics) || stristr($lyrics, 'we do not have the lyric for this song') || stristr($lyrics, 'lyrics are currently unavailable') || stristr($lyrics, 'your name will be printed as part of the credit'))
	{
		$no_match = true;
	}
	else
	{
		if(strstr($lyrics, 'Ã') && strstr($lyrics, '©')) $lyrics = utf8_decode($lyrics);

		$lyrics = trim(str_replace('<br />', '<br>', $lyrics));
		$lyrics = preg_replace('/<br>---.*/', '', $lyrics);

		$count = get_db_count('lyrics-cache', "SELECT COUNT(id) as count FROM lyrics WHERE md5 = '" . md5($uri) . "'");
		if($count == 0) db_exec('lyrics-cache', "INSERT INTO lyrics (md5, lyrics) VALUES ('" . md5($uri) . "', '" . sqlite_escape(base64_encode($lyrics)) . "')");
	}
}

$activity = array();
$activity['title'] = hsc($title);

if($error)
{
	$activity['actions'][] = array('action' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));
	$content = '<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Could not get lyrics. Try again.</div></div>';
}
elseif($no_match)
{
	$activity['actions'][] = array('action' => array('Search the web', 'internet_32_img_div'), 'keys' => array('actions', 'uri'), 'values' => array('open_external_activity', 'http://www.google.com/search?q=' . rawurlencode($artist . ' ' . $title . ' lyrics')));
	$content = '<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>No match</div></div>';
}
else
{
	$content = '<div id="lyrics_div">' . $lyrics . '</div>';
}

echo '<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">' . $content . '</div>';

?>
