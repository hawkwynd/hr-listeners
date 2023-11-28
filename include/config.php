<?php
// set variables here.
// Shoutcast server defines

// url to your shoutcast server, for statistic json call.
define('SC_SERV_STATISTICS', 'http://hostname:8000/statistics?json=1');

// Shoutcast server admin call for data
define('SC_SERV_ADMIN', 'http://hostname:8000/admin.cgi?mode=bandwidth&sid=1&pass=mySCPassword&type=json');

// Mysql defines
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'mysql_username');
define('MYSQL_PASSWD', 'your_mysql_password');
define('MYSQL_DB', 'my_radiostation_db');

// api keys for ipstack
define('API_KEY_STACK', 'your api key here');
define('API_URL', 'http://api.ipstack.com/');

// URL for https://api.geoiplookup.net/?query=94.123.47.237&json=true

DEFINE('GEOIPLOOKUP_URL', 'https://api.geoiplookup.net/?query=');
DEFINE('GEOIP_ARGS', '&json=true');

// Google API Key (optional)
DEFINE('GOOGLE_API_KEY', 'your google developer api key');