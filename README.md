# hr-listeners

Database files are found in the mysql folder. Import these into your database for use with the HR-Listeners 


## Requirements ## 
```
include/config.php 

define('SC_SERV_STATISTICS', 'http://your_url:port/statistics?json=1');

define('SC_SERV_ADMIN', 'http://your_url:port/admin.cgi?mode=bandwidth&sid=1&pass=password&type=json');

// Mysql defines

define('MYSQL_HOST', 'your_mysql_host');

define('MYSQL_USER', 'mysql_username');

define('MYSQL_PASSWD', 'mysql-password');

define('MYSQL_DB', 'your-database-name');
```