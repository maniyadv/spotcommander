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

$code = (isset($_GET['code'])) ? intval($_GET['code']) : 0;
$message = 'Unknown error.';

if($code == 1)
{
	$message = 'There is something wrong with your config.php file. Start over with a fresh config.php, and make sure that you read the text for each option carefully.';
}
elseif($code == 2)
{
	$message = 'Can not connect to the daemon. Follow the installation instructions carefully.';
}
elseif($code == 3)
{
	$message = 'It looks like files and/or folders that should be writeable can not be written to. Follow the installation instructions carefully.';
}
elseif($code == 4)
{
	$message = 'QDBus can not be found. Please report an issue.';
}
elseif($code == 5)
{
	$message = 'You must enable cookies in your browser.';
}
elseif($code == 6)
{
	$message = 'You must enable JavaScript in your browser.';
}

$sysinfo = get_system_information();
$files = get_external_files(array(project_website . 'api/1/error/?version=' . rawurlencode(number_format(project_version, 1)) . '&error_code=' . rawurlencode($code) . '&uname=' . rawurlencode($sysinfo['uname']) . '&ua=' . rawurlencode($sysinfo['ua'])), null, null);

?>

<!DOCTYPE html>

<html>

<head>

<title><?php echo project_name; ?></title>

<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="x-ua-compatible" content="IE=edge">

<meta name="viewport" content="user-scalable=no, initial-scale=1.0">

<link rel="shortcut icon" href="img/favicon.ico?<?php echo project_serial ?>">

<link rel="stylesheet" href="css/style-error.css?<?php echo project_serial ?>">
<link rel="stylesheet" href="css/style-images.css?<?php echo project_serial; ?>">

</head>

<body>

<div class="img_div img_64_div error_64_img_div"></div>

<div id="box_div">
<div id="box_inner_div">

<div id="box_title_div">Oops!</div>

<div id="box_body_div">
<div><?php echo $message; ?></div>
<div>When the problem is fixed, tap retry below.</div>
</div>

<div id="box_buttons_div">
<div><a href="." onclick="window.location.replace('.'); return false">Retry</a></div>
<div><a href="<?php echo project_website; ?>?install" target="_blank">Help</a></div>
<div><a href="<?php echo project_website; ?>?issues" target="_blank">Report</a></div>
</div>

</div>
</div>

</body>

</html>
