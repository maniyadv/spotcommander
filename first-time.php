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

$activity = array();
$activity['title'] = 'Welcome';
$activity['actions'][] = array('action' => array('OK, got it!', 'yes_32_img_div'), 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('replace_activity', 'playlists', '', ''));

?>

<div id="activity_inner_div" data-activitydata="<?php echo base64_encode(json_encode($activity)); ?>">

<div class="list_header_div"><div><div>NOTES</div></div><div></div></div>

<ul>
<li>Learn how to use <?php echo project_name; ?> below</li>
<li>This information will only show once</li>
<li>You can find this information later by tapping help in the menu</li>
</ul>

<div class="list_header_div"><div><div>BASIC NAVIGATION</div></div><div></div></div>

<ul>
<li>Tap the icon to the left on the top action bar to show the menu</li>
<li>Tap the text on the bottom action bar to show/hide now playing + remote</li>
<li>When an activity has more options, an icon will appear to the right on the top action bar</li>
<li>Tap the top action bar to scroll to the top of the activity</li>
</ul>

<div class="list_header_div"><div><div>GESTURES</div></div><div></div></div>

<ul>
<li>Swipe in from the left to show the menu</li>
<li>Swipe up from the bottom to show now playing + remote</li>
<li>Swipe left on now playing cover art to play next track</li>
</ul>

<div class="list_header_div"><div><div>KEYBOARD SHORTCUTS</div></div><div></div></div>

<ul>
<li>Z: Previous track</li>
<li>X: Play/pause</li>
<li>C: Next track</li>
<li>1: Mute</li>
<li>2: Volume down</li>
<li>3: Volume up</li>
<li>Q: Playlists</li>
<li>W: Library</li>
<li>E: Discover</li>
<li>R: Search</li>
<li>A: Now playing + remote</li>
<li>S: Recently played</li>
<li>D: Queue</li>
</ul>

<div class="list_header_div"><div><div>MOUSE SHORTCUTS</div></div><div></div></div>

<ul>
<li>Right-click on list items to show the actions overflow dialog</li>
</ul>

</div>
