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

if(isset($_GET['queue_uri']))
{
	echo queue_uri(rawurldecode($_POST['artist']), rawurldecode($_POST['title']), $_POST['uri']);
}
elseif(isset($_GET['queue_uris']))
{
	echo queue_uris($_POST['uris'], $_POST['randomly']);
}
elseif(isset($_GET['move']))
{
	echo move_queued_uri($_POST['id'], $_POST['sortorder'], $_POST['direction']);
}
elseif(isset($_GET['remove']))
{
	echo remove_from_queue($_POST['id'], $_POST['sortorder']);
}
elseif(isset($_GET['clear']))
{
	echo clear_queue();
}
else
{
	$activity = array();
	$activity['title'] = 'Queue';
	$activity['actions'][] = array('action' => array('Clear', 'delete_32_img_div'), 'keys' => array('actions'), 'values' => array('clear_queue'));

	$tracks = get_db_rows('queue', "SELECT id, artist, title, uri, sortorder FROM queue ORDER BY sortorder, id", array('id', 'artist', 'title', 'uri', 'sortorder'));

	if(empty($tracks))
	{
		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>No queued tracks</div></div>

			</div>
		';
	}
	else
	{
		echo '
			<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

			<div class="list_header_div"><div><div>ALL</div></div><div></div></div>

			<div class="list_div">
		';

		foreach($tracks as $track)
		{
			$id = $track['id'];
			$artist = $track['artist'];
			$title = $track['title'];
			$uri = $track['uri'];
			$sortorder = $track['sortorder'];

			echo '
				<div class="list_item_div">
				<div title="' . hsc($artist . ' - ' . $title) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
				<div class="list_item_main_actions_arrow_div"></div>
				<div class="list_item_main_corner_arrow_div"></div>
				<div class="list_item_main_inner_div">
				<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div ' . track_is_playing($uri, 'icon') . '"></div></div>
				<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div ' . track_is_playing($uri, 'text') . '">' . hsc($title) . '</div><div class="list_item_main_inner_text_lower_div">' . hsc($artist) . '</div></div>
				</div>
				</div>
				<div class="list_item_actions_div">
				<div class="list_item_actions_inner_div">
				<div title="Move up" class="actions_div" data-actions="move_queued_uri" data-id="' . $id . '" data-sortorder="' . $sortorder . '" data-direction="up" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div up_24_img_div"></div></div>
				<div title="Move down" class="actions_div" data-actions="move_queued_uri" data-id="' . $id . '" data-sortorder="' . $sortorder . '" data-direction="down" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div down_24_img_div"></div></div>
				<div title="Move to top" class="actions_div" data-actions="move_queued_uri" data-id="' . $id . '" data-sortorder="' . $sortorder . '" data-direction="top" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div totop_24_img_div"></div></div>
				<div title="Remove" class="actions_div" data-actions="remove_from_queue" data-id="' . $id . '" data-sortorder="' . $sortorder . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div delete_24_img_div"></div></div>
				</div>
				</div>
				</div>
			';
		}

		echo '</div></div>';
	}
}

?>
