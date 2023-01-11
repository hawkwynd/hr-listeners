<?php

require 'config.php';


class Geoip {

    public $key;

    function __construct(){
        $this->key = API_KEY_STACK;
        $this->url = API_URL;
    }


    public function lookup( $ip ){
        $url    = API_URL;
        $key    = API_KEY_STACK;
        $json   = file_get_contents( $url.$ip."?access_key=".$key );
        $data   = json_decode( $json, true );

        return $data; 

    }
    
}


