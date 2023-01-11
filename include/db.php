<?php 

// https://geolocation-db.com/dashboard
// key = 89eb4d70-4cbe-11ed-a0f2-51b843ebe8d7

require 'config.php';
require 'class-geo.php';

/**
 * 
 * MYSQL CONNECTIONS and queries
 */

 $mysqli = new mysqli( MYSQL_HOST, MYSQL_USER, MYSQL_PASSWD, MYSQL_DB );
 $mysqli->query("SET time_zone = 'America/Chicago'");

 $agents = array(
    'EMPTY'             => 'flag-icon flag-icon-empty',
    'Unknown'           => 'flag-icon flag-icon-unknown',
    'okhttp'            => 'flag-icon flag-icon-okhttp',
    'Apple'             => 'flag-icon flag-icon-apple',
    'axios'             => 'flag-icon flag-icon-axios',
    'mozilla'           => 'flag-icon flag-icon-mozilla',
    'SHOUTcast'         => 'flag-icon flag-icon-shoutcast',
    'Winamp'            => 'flag-icon flag-icon-nullsoft',
    'Cloud'             => 'fab fa-soundcloud',
    'vlc'               => 'flag-icon flag-icon-vlc',    
    'Player'            => 'fa fa-drum',
    'Kodi'              => 'flag-icon flag-icon-kodi',
);


 if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
  }


  function GetUseragents($out = array()){
      global $mysqli;
      if( !$result = $mysqli->query( "SELECT * FROM useragents" ) ) throw new Exception( $mysqli->error );
        $payload = $result->fetch_all();
      return $payload;
  }



function agentKeys(){
    global $agents;
    $payload = "";

    foreach( $agents as $key => $icon ){

        $payload .= sprintf("<i class='%s'></i><span class='agentkey'>%s</span>", $icon, $key);

    }

    return $payload;
}



function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}


function nowPlaying(){
    try{
    
        global $mysqli;
        if( !$result = $mysqli->query( "SELECT * FROM nowplaying" ) ) throw new Exception( $mysqli->error );
        return $result->fetch_object();

    } catch( Exception $e){
        return  $e->getMessage() ;
    }
}


function listeners_today( $mObj, $since  ){
    
    try{

        global $mysqli;
        global $useragents;

        $plays      = plays();
        $totals     = listeners_total();
        $nowplaying = nowPlaying();
        $out        = [];

		$result    = $mysqli->query("select * from listeners_today");

		if(!$result) throw new Exception( $mysqli->error );       

		if( $result->num_rows > 0 ){
			while($row = $result->fetch_object() ){ 
                
                // Remove unwanted fields from our output array. 
				unset( $row->timestamp, $row->referer);

				array_push($out, $row); 
			}
            
            
			$header = array_keys( get_object_vars($out[0]) );
			$cols   = $data ='';

			foreach($header as $col){
				
                // remove pretty_country header we dont want to display that
                // but we're going to use the data in our display later. 

                if($col == 'pretty_country') continue;
                if($col == 'hostname') continue;

				switch($col){
                    
                    case 'state':
                        $col = 'Region';
                        break;
					case 'fconnect':
						$col = '1st<br/>Visit';
						break;
					case 'dtime':
						$col = 'Disconnect';
						break;
                    case 'useragent':
                        $col = 'Agent';
                        break;
                    case 'duration':
                        $col = 'Duration';
                        break;
                    case 'connection_count':
                        $col = 'Visits';
                        break;
                    case 'last_time':
                        $col = 'Last<br/>Disconnect';
                        break;
                    case 'connecttime':
                        $col = 'Last<br/>Duration';
					default:
						break;
				}

				$cols .= sprintf('<th>%s</th>', $col );
			}
        
			foreach($out as $id => $row ){
				
                $now      =  new DateTime('now', new DateTimezone('America/Chicago'));
				$fconnect = convertStamp( $row->fconnect );             
				$now      = convertStamp( $now->format('Y-m-d'));

				sscanf( $row->duration, "%d:%d:%d", $hours, $minutes, $seconds);

				$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
				
				// gt 1min connected
				if( $time_seconds > -1 || $row->dtime == '0000-00-00 00:00:00'){
				
					$data .=  sprintf( "<tr id='$id' %s data-hostname='%s' data-pretty-country='%s'>", $fconnect == $now ? 'class="firsttimer"' : '' , $row->hostname, $row->pretty_country );

					foreach($row as $key => $datacol ){          
                        
                        if($key == 'pretty_country') continue;
                        if($key == 'hostname') continue;

                        if($key == 'country'){
                            $datacol = sprintf('<span class="flag-icon flag-icon-%s flag-icon-squared"></span> %s', strtolower($datacol), $row->pretty_country);
                        }

                        // display last connecttime ago standards
                        if( $key == 'last_time' && $datacol !== '0000-00-00 00:00:00' ){
                            $datacol = time_elapsed_string($datacol);
                        }
                        if( $key == 'last_time' && $datacol == '0000-00-00 00:00:00'){
                            $datacol = 'Today';
                        }
						if($key == 'fconnect'){
							$pretty = new DateTime( $datacol );
							$datacol = $pretty->format('m-d-y');
						}

						if($key == 'dtime'){
							if($datacol == '0000-00-00 00:00:00') {
							   $datacol = 'Listening';
							}else{
							   $pretty = new DateTime( $datacol );
							   $datacol = $pretty->format('h:i A');
							}
						}

                        if($key == 'useragent'){
                            $datacol = formatAgent( $datacol );
                        }


						$data .= sprintf('<td %s>%s %s</td>', 
						$datacol == 'Listening' ? 'class=connected' : '',
                        $datacol == 'Masked' ? '<span class="lookup flag-icon flag-icon-unknown"></span>' : '',
						$key == 'disconnect' ? ( $datacol == 'Listening' ? 'Connected' : $datacol ) : ($datacol == 'null' ? 'Unknown' : $datacol ) 
                        
                        ); 
					
                        
                    
                    }  
				
					$data .= "</tr>";

				} // if time > 60 
			}
    
            // render table with data nuggets

        //    echo "<pre>", print_r( $out ), "</pre>";

            $todays_listeners = count( $out );
			
            return sprintf("<div>
                                <table class='first table table-bordered '>
                                <caption>
                                    <div class='nowplaying'>Playing Right Now: %s</div>
                                    <div class='stats'><span>Songs Played: %s</span><span>Listeners today: %d</span><span>%s listeners: %d</span><span>Total Listeners: %s</span></div>
                                    <div class='agentContainer'>%s</div>
                                </caption>
							   <thead class='thead-dark'><tr>%s</tr></thead>
                               <tbody>%s</tbody>
                            </table></div>", 
							$nowplaying->cursong,
							number_format($plays->plays), 
                            $todays_listeners, 
							$mObj->MonthName, 
							$mObj->count, 
							number_format( $totals->totalListeners ), 
                            agentKeys(),
							$cols, 
							$data
                            
						);  
		}else{

			return('<div>No Listeners! Something surely must be wrong for me to show this!</div>');

		} // if result > 0 
    
    }catch (Exception $e){
        return $e->getMessage();
    }
}

function convertStamp( $stamp ){
    $dt = date_create($stamp);
    return $dt->format('m-d');
}



// Proper formatting of useragent string for icons

function formatAgent($agent, $myClass = 'flag-icon flag-icon-fuck'){

    global $agents;

    // Distinct user agents from useragents view
    // $useragents = GetUseragents(); 


    foreach($agents as $key => $class){
        if( stripos($agent, $key) !== false ){
            $myClass = $class;
        }
    }

    return '<div class="text-center" data-agent="'.$agent.'"><i class="'.$myClass.'" data-type="'.$agent.'"></i></div>';

}


function listeners_yesterday() {

    global $mysqli;
    $out       = $countries = [];
    $result    = $mysqli->query("SELECT * FROM listeners_yesterday WHERE country IS NOT NULL ORDER BY connecttime DESC");
    $cols      = $cPack = $data ='';

    while($row = $result->fetch_object() ){        
        
        // drop timestamp and disconnect from array
        unset($row->TIMESTAMP, $row->disconnect );
        array_push($out, $row); 
        
        // collect countries array
		array_push($countries, $row->country );

    }

    $header = array_keys( get_object_vars($out[0]) );
    
    foreach($header as $col){
        $col = $col == 'state' ? 'Region' : $col; 
        $cols .= sprintf('<th>%s</th>', $col == 'connecttime' ? 'Duration' : $col );
    }
    
    // count of countries listening
    $countryCount = array_count_values( $countries );
    $cPack = "<div class='stats'>";

    foreach($countryCount as $k => $v ){
        $cPack .= sprintf('<span><span class="flag-icon flag-icon-%s flag-icon-squared"></span> %s:%d</span>', strtolower($k), $k, $v);
    }
    $cPack .= "</div>";
    
    foreach($out as $id => $row ){      
        
        sscanf($row->connecttime, "%d:%d:%d", $hours, $minutes, $seconds);
        $time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
        
        // filter out rows < 120 seconds listening time
        // gt 5min connected
        // if( $time_seconds > 300 ){
            $data .= "<tr>";     

            foreach($row as $key => $datacol ){       
                $data .= sprintf('<td>%s</td>', $datacol == 'null' ? '' : $datacol ); 
            }  
            $data .= "</tr>";
        // }
    }
   
   return sprintf("<div>
                    <table class='second table table-bordered'>
                        <caption>
                            Yesterday total listeners: %d   
                            <div> %s </div>
                        </caption>
                    </table>
                  </div>", 
                count($out), $cPack);  
}



function current_month_count(){
	global $mysqli;
	$q = "SELECT MONTHNAME(CURRENT_DATE) as `MonthName`, COUNT(*) AS `count` FROM `listeners_this_month`";
	$result = $mysqli->query($q) or die( 'this_month_count error: ' . mysqli_error($mysqli));
	return $result->fetch_object();
}



function analytics(){
    global $mysqli;

    $monthName = date('F');

    $result = $mysqli->query(
        "SELECT FORMAT(a.connection_count, 0) `total visits`, l.city, l.state, l.country, DATE_FORMAT(l.first_connect, '%m-%d-%y') 'first visit', 
        DATE_FORMAT(l.disconnect, '%m-%d-%y') 'last visit'
         FROM analytics a
         JOIN listeners l on l.id=a.id 
         WHERE l.timestamp BETWEEN (CURRENT_DATE() - INTERVAL 1 MONTH) AND CURRENT_DATE()
         ORDER BY a.connection_count DESC LIMIT 10"
    );

    $out=[];

    while($row = $result->fetch_object() ){
        array_push($out, $row);
    }

    // return $out;
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    foreach($header as $col){
        $col = $col == 'state' ? 'Region' : $col; 
        $cols .= sprintf('<th>%s</th>', $col == 'connecttime' ? 'Duration' : $col );
    }
   
    foreach($out as $id => $row ){      
       $data .= "<tr>";     
       foreach($row as $key => $datacol ){       
           $data .= sprintf('<td>%s</td>', $datacol);
                //    $key == 'disconnect' ? 'class=disconnect':'',  
                //    $key == 'disconnect' ? ($datacol == '0000-00-00 00:00:00' ? 'Listening' : $datacol) : $datacol == 'null' ? 'Unknown' : $datacol ); 
       }  
   
       $data .= "</tr>";
    }
   
   return sprintf("<div><table class='table table-responsive'><caption>Top 10 %s Listeners by Visits</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $monthName, $cols, $data);  
}


// last 72hr listeners
function last72(){
    global $mysqli;

    $result = $mysqli->query(
        "SELECT * FROM `last72_hr_listeners`"
    );

    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            
            switch($key){
                case "useragent":
                    $agent = explode('/', $datacol);
                    $d     = explode(',',$agent[0]);
                    $datacol = $d[0];

            }

            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Last 72hrs by Duration</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}


// Top US listeners

function top_US_listeners(){
    global $mysqli;
    $result = $mysqli->query(
        "SELECT
        l.city,
        l.state 'state or name',
        FORMAT(a.connection_count,0) visits
    FROM
        `listeners` l
    JOIN analytics a ON
        a.id = l.id
    WHERE
        l.country IN('US')
    GROUP BY
        l.city
    ORDER BY
        a.connection_count
    DESC
    LIMIT 10"
    );

    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Top 10 All Time US by Visits</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}

// Top Jarvis Song Plays
function top_jarvis_plays(){
    global $mysqli;
    $result = $mysqli->query(
        "SELECT * FROM `top_song_plays` limit 14"
    );
    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Jarvis All Time Most Played Songs </caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}

// Last Hour plays 
function last_hour_plays(){
    global $mysqli;
    $result = $mysqli->query(
        "SELECT DATE_FORMAT(last_played, '%h:%i%p') played, artist, song, plays FROM `plays_today`
        where last_played >  now() - interval 1 hour"
    );
    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            // $datacol = $datacol == "0" ? "1": $datacol;
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>Last Hour Jarvis Plays</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $cols, $data); 
}

// Top 10 listeners by Country
function top_10_by_country(){
    global $mysqli;

    $monthName = date('F');

    $result = $mysqli->query(
        "SELECT * FROM `top-10-countries-listeners`"
    );
    $out = [];

    while( $row = $result->fetch_object() ){
        array_push($out, $row);
    }
    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    // build headers
    foreach($header as $col){
       $cols .= sprintf('<th>%s</th>', $col );
    }
    foreach($out as $id => $row ){      
        $data .= "<tr>";     
        foreach($row as $key => $datacol ){       
            $data .= sprintf('<td>%s</td>', $datacol );
        }  
    
        $data .= "</tr>";
    }

    return sprintf("<div><table class='table table-responsive'><caption>%s Top 10 Countries</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>", $monthName, $cols, $data); 
}


// top_ten listeners from view
function top_10(){

	global $mysqli;
    $monthName = date('F');

	$result = $mysqli->query("SELECT * FROM `listeners_top_10_connections`");
	$out       = [];

    while($row = $result->fetch_object() ){ 
        
        // drop timestamp from array, for now as we dont want them.
        unset($row->timestamp, $row->referer);
       array_push($out, $row); 
    }

    $header = array_keys( get_object_vars($out[0]) );
    $cols   = $data ='';
   
    foreach($header as $col){
        $col = $col == 'state' ? 'Region' : $col; 
       $cols .= sprintf('<th>%s</th>', $col == 'connecttime' ? 'Duration' : $col );
    }
   
    foreach($out as $id => $row ){      
       $data .= "<tr>";     
       foreach($row as $key => $datacol ){       
           $data .= sprintf('<td %s>%s</td>', 
                   $key == 'disconnect' ? 'class=disconnect':'',  
                   $key == 'disconnect' ? ($datacol == '0000-00-00 00:00:00' ? 'Listening' : $datacol) : $datacol == 'null' ? 'Unknown' : $datacol ); 
       }  
   
       $data .= "</tr>";
    }
   
   return sprintf("<div><table class='table table-responsive'><caption>Top 10 %s Listeners by Duration</caption>
                    <thead class='thead-dark'><tr>%s</tr></thead><tbody>%s</tbody></table></div>",$monthName, $cols, $data);  

}

/**
 * @return 
 * date: firstConnect
 * date: lastConnect 
 * integer: totalListeners
 */


function time_elapsed_string($datetime, $full = false) {
	
	$now = new DateTime;
	$now->setTimezone(new DateTimeZone('America/Chicago'));
    $ago = new DateTime($datetime);
	
	// Subtract time from datetime
	$ago->modify("+6 hours");

    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hr',
        'i' => 'min',
        's' => 'sec',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    

	$output = $string ? implode(', ', $string) . ' ago' : 'just now';

	return  $output;
}

function listeners_total(){
	global $mysqli;
	$result = $mysqli->query("SELECT * FROM `listeners_total_count`");
	return $result->fetch_object();
}


function listeners_since(){
	global $mysqli;
	$result = $mysqli->query("SELECT COUNT(hostname) total, MIN(timestamp) since  FROM `listeners`") or die("listeners_since error: " . mysqli_error($mysqli));
	return $result->fetch_object();
}

function totalTime( $tabl ){
    global $mysqli;

    $result = $mysqli->query( "SELECT TIME_FORMAT( SEC_TO_TIME(SUM(TIME_TO_SEC(connecttime))), '%T') AS TotalTime FROM $tabl"  );   
    $out = $result->fetch_object();
    return $out->TotalTime;
}

function plays(){
    global $mysqli;

    try{
        $sql = "SELECT SUM(plays) plays from recording";
        $result = $mysqli->query( $sql );
        if(!$result) throw new Exception( $mysqli->error() );

        $plays = $result->fetch_object();
        return $plays;
    }
    catch( Exception $e ){
        return $e->getMessage(); 
    }
}

function getListener( $hostname ){
    global $mysqli;

    try{
        $result = $mysqli->query(
            "SELECT * from listeners WHERE hostname='$hostname'"
        );

        // return  json_encode( $result->fetch_object() );
        
        updateListener( $result->fetch_object() );

    } catch( Exception $e) {
        return $e->getMessage();
    }

}

/**
 * 
 * ipstack.com data results
 * 
 * city: "Santa Clara"
 * continent_code : "NA"
 * continent_name : "North America"
 * country_code : "US"
 * country_name : "United States"
 * ip : "198.235.24.184"
 * latitude : 37.39630889892578
 * location : {geoname_id: 5393015, capital: 'Washington D.C.', languages: Array(1), country_flag: 'https://assets.ipstack.com/flags/us.svg', country_flag_emoji: '🇺🇸', …}
 * longitude : -121.9614028930664
 * region_code : "CA"
 * region_name : "California"
 * type : "ipv4"
 * zip : "95054"

 */

function updateListener( $data, $message = [] ){
    global $mysqli;

    $current    = $data;
    $hostname   = $data['ip'];
    $city       = $data['city'] == '' ? 'Masked' : $data['city'];
    $state      = $data['region_name'] == '' ? 'Masked' : $data['region_name'];
    $country    = $data['country_code'] == '' ? 'Masked' : $data['country_code'];
    $lat        = $data['latitude'];
    $lng        = $data['longitude'];
    
    $sql = "UPDATE listeners SET city='$city', state='$state',country='$country', lat=$lat,lng=$lng WHERE hostname IN('$hostname')";
    
    try{

        
        $update = $mysqli->query( $sql );

        $message = array(
            'ip'          => $data['ip'],
            'city'          => $city,
            'state'         => $state,
            'country_code'  => $country,
            'latitude'      => $lat,
            'longitude'     => $lng
        );


    } catch( Exception $e ){
        return $e-getMessage();
    }

    return json_encode( $message ); 

    // exit;

}
 