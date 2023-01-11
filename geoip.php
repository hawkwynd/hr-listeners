<?php 

require 'include/config.php';

$ip = "91.59.179.16";

$data = file_get_contents( API_URL.$ip."?access_key=". API_KEY_STACK );

echo $data; 
