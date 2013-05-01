<?php

// Set to false to not send information about your system when checking for updates and when there is a fatal error
// By default it sends your browser's user agent and the output of the command "uname -mrsv" on your server
// This information is useful when developing and testing this app
define('config_send_system_information', true);

// Set to true if your computer connects to the Internet through an explicit proxy
// If set to true, proxy details must be configured below
define('config_proxy', false);

// Proxy address
define('config_proxy_address', '192.168.0.1');

// Proxy port
define('config_proxy_port', 8080);

?>
