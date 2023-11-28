# hr-listeners

Database files are found in the mysql folder. 
Import these into your database for use with the HR-Listeners.

## Mysql Views
`activeListeners.sql`
This is a view to return the active listeners from the listeners table.

`analytics.sql`
This table holds the data sent by the node.js script which monitors the shoutcast connections
and updates this table.



## Requirements ## 
include/config.php -- edit this file and configure your settings for shoutcast server access and mysql database.


```
define('SC_SERV_STATISTICS', 'http://your_url:port/statistics?json=1');
define('SC_SERV_ADMIN', 'http://your_url:port/admin.cgi?mode=bandwidth&sid=1&pass=password&type=json');

// Mysql defines
define('MYSQL_HOST', 'your_mysql_host');
define('MYSQL_USER', 'mysql_username');
define('MYSQL_PASSWD', 'mysql-password');
define('MYSQL_DB', 'your-database-name');
```