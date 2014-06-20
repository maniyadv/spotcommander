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

?>

<!DOCTYPE html>

<html>

<head>

<title><?php echo project_name ?></title>

<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="x-ua-compatible" content="IE=edge">

<noscript><meta http-equiv="refresh" content="0; url=error.php?code=6"></noscript>

<meta name="viewport" content="user-scalable=no, initial-scale=1.0">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="msapplication-tap-highlight" content="no">
<meta name="msapplication-TileImage" content="img/favicon-144.png?<?php echo project_serial; ?>">
<meta name="msapplication-TileColor" content="#303030">

<script src="js/jquery.js?<?php echo project_serial; ?>"></script>
<script src="js/jquery-cookie.js?<?php echo project_serial; ?>"></script>
<script src="js/jquery-base64.js?<?php echo project_serial; ?>"></script>
<script src="js/jquery-easing.js?<?php echo project_serial; ?>"></script>
<script src="js/modernizr.js?<?php echo project_serial; ?>"></script>
<script src="js/functions.js?<?php echo project_serial; ?>"></script>
<script src="js/main.js?<?php echo project_serial; ?>"></script>

<link rel="shortcut icon" href="img/favicon.ico?<?php echo project_serial; ?>">
<link rel="shortcut icon" href="img/favicon-128.png?<?php echo project_serial; ?>" sizes="128x128">
<link rel="shortcut icon" href="img/favicon-196.png?<?php echo project_serial; ?>" sizes="196x196">

<link rel="apple-touch-icon" href="img/touch-icon-57.png?<?php echo project_serial; ?>" sizes="57x57">
<link rel="apple-touch-icon" href="img/touch-icon-72.png?<?php echo project_serial; ?>" sizes="72x72">
<link rel="apple-touch-icon" href="img/touch-icon-76.png?<?php echo project_serial; ?>" sizes="76x76">
<link rel="apple-touch-icon" href="img/touch-icon-114.png?<?php echo project_serial; ?>" sizes="114x114">
<link rel="apple-touch-icon" href="img/touch-icon-120.png?<?php echo project_serial; ?>" sizes="120x120">
<link rel="apple-touch-icon" href="img/touch-icon-144.png?<?php echo project_serial; ?>" sizes="144x144">
<link rel="apple-touch-icon" href="img/touch-icon-152.png?<?php echo project_serial; ?>" sizes="152x152">
<link rel="apple-touch-startup-image" href="img/splash-iphone-old.png?<?php echo project_serial; ?>" media="screen and (device-width: 320px) and (device-height: 480px)">
<link rel="apple-touch-startup-image" href="img/splash-iphone-old-2x.png?<?php echo project_serial; ?>" media="screen and (device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="img/splash-iphone-new.png?<?php echo project_serial; ?>" media="screen and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="img/splash-ipad-portrait.png?<?php echo project_serial; ?>" media="screen and (device-width: 768px) and (device-height: 1024px) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="img/splash-ipad-portrait-2x.png?<?php echo project_serial; ?>" media="screen and (device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="img/splash-ipad-landscape.png?<?php echo project_serial; ?>" media="screen and (device-width: 768px) and (device-height: 1024px) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="img/splash-ipad-landscape-2x.png?<?php echo project_serial; ?>" media="screen and (device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)">

<link rel="stylesheet" href="css/style.css?<?php echo project_serial; ?>">
<link rel="stylesheet" href="css/style-images.css?<?php echo project_serial; ?>">
<link rel="stylesheet" href="css/style-animations.css?<?php echo project_serial; ?>">

<style></style>

</head>

<body>

<div id="top_actionbar_div">
<div id="top_actionbar_inner_div">
<div id="top_actionbar_inner_left_div"><div><div title="Menu" class="actions_div" data-actions="toggle_menu" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div menu_32_img_div"></div></div></div></div>
<div id="top_actionbar_inner_center_div" class="actions_div" data-actions="scroll_to_top" onclick="void(0)"><div></div></div>
<div id="top_actionbar_inner_right_div"><div></div></div>
</div>
<div id="top_actionbar_shadow_div"></div>
</div>

<div id="bottom_actionbar_div">
<div id="bottom_actionbar_inner_div">
<div id="bottom_actionbar_inner_left_div"><div><div title="Play/pause" class="actions_div" data-actions="remote_control" data-remotecontrol="play_pause" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div play_32_img_div"></div></div></div></div>
<div id="bottom_actionbar_inner_center_div" class="actions_div" data-actions="toggle_nowplaying" data-highlightotherelement="div#bottom_actionbar_inner_div" data-highlightotherelementparent="div#bottom_actionbar_div" data-highlightotherelementclass="green_highlight" onclick="void(0)"><div></div></div>
<div id="bottom_actionbar_inner_right_div"><div><div title="Refresh" class="actions_div" data-actions="refresh_nowplaying" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div reload_32_img_div"></div></div></div></div>
</div>
</div>

<div id="menu_div">
<div class="menu_item_div menu_big_item_div actions_div" data-actions="change_activity" data-activity="playlists" data-subactivity="" data-args="" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="menu_big_item_indicator_div"></div><div class="menu_big_item_icon_div"><div class="img_div img_48_div playlist_48_img_div"></div></div><div class="menu_big_item_text_div">Playlists</div></div>
<div class="menu_item_div menu_big_item_div actions_div" data-actions="change_activity" data-activity="starred" data-subactivity="" data-args="" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="menu_big_item_indicator_div"></div><div class="menu_big_item_icon_div"><div class="img_div img_48_div star_48_img_div"></div></div><div class="menu_big_item_text_div">Starred</div></div>
<div class="menu_item_div menu_big_item_div actions_div" data-actions="change_activity" data-activity="discover" data-subactivity="" data-args="" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="menu_big_item_indicator_div"></div><div class="menu_big_item_icon_div"><div class="img_div img_48_div discover_48_img_div"></div></div><div class="menu_big_item_text_div">Discover</div></div>
<div class="menu_item_div menu_big_item_div actions_div" data-actions="change_activity" data-activity="search" data-subactivity="" data-args="" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="menu_big_item_indicator_div"></div><div class="menu_big_item_icon_div"><div class="img_div img_48_div search_48_img_div"></div></div><div class="menu_big_item_text_div">Search</div></div>
<div id="menu_first_small_item_div" class="menu_item_div menu_small_item_div actions_div" data-actions="change_activity" data-activity="settings" data-subactivity="" data-args="" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div><div id="update_available_indicator_div" class="img_div img_24_div settings_24_img_div"></div></div><div>SETTINGS</div></div>
<div class="menu_item_div menu_small_item_div actions_div" data-actions="open_external_activity" data-uri="<?php echo project_website; ?>?use" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div><div class="img_div img_24_div help_24_img_div"></div></div><div>HELP</div></div>
</div>

<div id="top_actionbar_overflow_actions_div">
<div id="top_actionbar_overflow_actions_arrow_div"></div>
<div id="top_actionbar_overflow_actions_inner_div" class="actions_div" data-actions="hide_activity_overflow_actions"></div>
</div>

<div id="dialog_div"></div>
<div id="toast_div"><div><div></div></div></div>

<div id="transparent_cover_div" class="actions_div" data-actions="hide_transparent_cover_div" onclick="void(0)"></div>
<div id="black_cover_div"></div>
<div id="black_cover_activity_div" class="actions_div" data-actions="hide_black_cover_activity_div" onclick="void(0)"></div>

<div id="nowplaying_div">
<div id="nowplaying_actionbar_left_div"><div title="Launch/quit Spotify" class="actions_div" data-actions="remote_control" data-remotecontrol="launch_quit" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div power_32_img_div"></div></div></div>
<div id="nowplaying_actionbar_right_div"><div title="Recently played" class="actions_div" data-actions="change_activity" data-activity="recently-played" data-subactivity="" data-args="" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div history_32_img_div"></div></div></div>
<div id="nowplaying_upper_div">
<div>
<div id="nowplaying_cover_art_div" title="Unknown" class="actions_div" data-actions="browse_album" data-uri="" onclick="void(0)"></div>
</div>
</div>
<div id="nowplaying_lower_div">
<div>
<div id="nowplaying_title_div" title="Unknown" onclick="void(0)">Unknown</div>
<div id="nowplaying_artist_div" title="Unknown" onclick="void(0)">Unknown</div>
<div id="nowplaying_volume_div">
<div id="nowplaying_volume_adjust_div">
<div id="nowplaying_volume_slider_div"><input id="nowplaying_volume_slider" type="range" min="0" max="100" step="1" value="50"></div>
<div id="nowplaying_volume_buttons_div"><div id="nowplaying_volume_buttons_inner_div"><div title="Mute" class="actions_div" data-actions="adjust_volume" data-volume="mute" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div volume_mute_32_img_div"></div></div><div title="Volume down" class="actions_div" data-actions="adjust_volume" data-volume="down" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div volume_down_32_img_div"></div></div><div title="Volume up" class="actions_div" data-actions="adjust_volume" data-volume="up" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div volume_up_32_img_div"></div></div></div></div>
</div>
<div id="nowplaying_volume_level_div"><span id="nowplaying_volume_level_span">50</span> %</div>
</div>
<div id="nowplaying_remote_div">
<div title="Toggle shuffle" class="actions_div" data-actions="toggle_shuffle_repeat" data-remotecontrol="toggle_shuffle" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_24_div shuffle_24_img_div"></div></div>
<div title="Previous" class="actions_div" data-actions="remote_control" data-remotecontrol="previous" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_48_div previous_48_img_div"></div></div>
<div title="Play/pause" class="actions_div" data-actions="remote_control" data-remotecontrol="play_pause" data-highlightclass="green_highlight" onclick="void(0)"><div id="nowplaying_play_pause_div" class="img_div img_64_div play_64_img_div"></div></div>
<div title="Next" class="actions_div" data-actions="remote_control" data-remotecontrol="next" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_48_div next_48_img_div"></div></div>
<div title="Toggle repeat" class="actions_div" data-actions="toggle_shuffle_repeat" data-remotecontrol="toggle_repeat" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_24_div repeat_24_img_div"></div></div>
</div>
</div>
</div>
</div>

<div id="nowplaying_actionbar_overflow_actions_div">
<div id="nowplaying_actionbar_overflow_actions_arrow_div"></div>
<div id="nowplaying_actionbar_overflow_actions_inner_div" class="actions_div" data-actions="hide_nowplaying_overflow_actions"></div>
</div>

<div id="activity_div"></div>

<div id="preload_div">
<img id="cover_art_preload_img" src="img/album-24.png?<?php echo project_serial; ?>" alt="Image">
<img id="nowplaying_cover_art_preload_img" src="img/album-24.png?<?php echo project_serial; ?>" alt="Image">
</div>

</body>

</html>
