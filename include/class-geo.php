<?php

require 'config.php';


class Geoip {

    public $key;

    function __construct(){
        $this->key = "7ef7dd1a96401e1ceecb973b6472931c";
        $this->url = "http://api.ipstack.com/";
    }

    public function lookup( $ip ){
        $url    = "http://api.ipstack.com/";
        $key    = '7ef7dd1a96401e1ceecb973b6472931c';
        $json   = file_get_contents( $url.$ip."?access_key=".$key );
        $data   = json_decode( $json, true );

        return $data; 

    }
    
}


