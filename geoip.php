<?php 

require 'include/config.php';


// $ipstack_url = "http://api.ipstack.com/";
// $key = '7ef7dd1a96401e1ceecb973b6472931c';

echo $ipstack_url.$ip."?access_key=".$ipstack_key ."\n";

$data = file_get_contents( $ipstack_url.$ip."?access_key=".$ipstack_key );

print_r( $data );
$json = json_decode( $data, true );

echo "<pre>", print_r( $json ), "</pre>";