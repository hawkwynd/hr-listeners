
<?php 
/**
 * Listeners Dashboard TODAY
 */
require 'include/db.php';

error_reporting( E_STRICT );
ini_set('display_errors', 1);
header("Content-Type: text/html; charset=ISO-8859-1");

// variables from functions
 $mObj  = current_month_count();
 $since = listeners_since(); 
 $Geoip = new Geoip();

//  listen for a post request and respond, then exit;

if( isset( $_POST['data'] )){

    $data = $Geoip->lookup( $_POST['data'] );

    if( array_key_exists('error', $data ) ){
        echo json_encode( $data , true) ;
        exit;
    }

    $updatedJSON = updateListener( $data );
    echo $updatedJSON;
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawkwynd Radio - Listeners</title>
    
    <!-- Then bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.css">
    <!-- Then our overrides -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- jQuery first -->
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
    
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

    <!-- fontawesome  -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">

    <!-- flags-icons -->
    <link rel="stylesheet" href="css/flag-icons.css">


</head>
<body>

<div class="container-fluid">
    <div class="row header align-items-start" data-key="<?php echo $Geoip->key;?>" data-url="<?php echo $Geoip->url; ?>">
        <div class="col-2" id="toggle"></div>
        <div class="col-1" id="active"></div>
        <div class="col-1" id="disconnected"></div>
        <div class="col-1" id="link">
            <a target="_blank" href="./report.php"><i class="fa fa-book fa-fw"></i> HAL Analytics</a>
        </div>
        <div class="col-3" id="notice"></div>
        <div class="col text-right">HAL v2.0</div>
    </div>
<?php 

try{

    $json   = file_get_contents( SC_SERV_STATISTICS );
    
    if( !$uptime = json_decode($json) ) throw new Exception( 'No response from statistics. Check internet connection');
    
    $streams    = $uptime->streams;
    $upHrs      = gmdate("H", $streams[0]->streamuptime );
    $upMins     = gmdate("i", $streams[0]->streamuptime );
    $hits       = number_format($streams[0]->streamhits);
    $uptime     = sprintf('%d hours %d minutes', $upHrs, $upMins);
    $json       = file_get_contents( SC_SERV_ADMIN );
    $obj        = json_decode($json);

    // print( $json );
    // printf('<table class="table"><caption>Bandwidth Metrics - Stream Uptime %s  - Stream hits %s</caption>', $uptime, $hits );
    // print('<thead class="thead-light"><tr><th>Total Sent</th><th>Total Received</th><th>v1 Client(s) sent</th><th>v2 Client(s) sent</th><th>HTTP Client(s) sent</th></tr></thead>');
    // printf('<tr><td>%s</td><td>%s</td><td>%s</td>',  human_filesize($obj->sent), human_filesize($obj->recv), human_filesize($obj->clientsent->v1) );
    // printf('<td>%s</td><td>%s</td></tr></table>', human_filesize($obj->clientsent->v2), human_filesize($obj->clientsent->http) );

    echo listeners_today( $mObj, $since );
    
    echo listeners_yesterday();
    
    // echo top_10(); 

} catch ( Exception $e ) {

    die( sprintf('<div>%s</div>', $e->getMessage() ));
}


?>

</div><!--container-->


<script>
$(document).ready(function(){

    let key         = $('.header').attr('data-key');
    let GeoIpUrl    = $('.header').attr('data-url');

    console.log('%cH.A.L. is active, doing nothing at this point except setting the active listener color rows and moving connected rows to the top of the table', 'color:lightgreen');
    
    let toggleCookie = readCookie("toggle"); // to retrive data from cookie
    
    // set rows that are connected.
    $('tbody tr:has(td.connected)').addClass('connected');


    // get count of disconnected rows
    let hidden = $('tbody tr:not(.connected)').length;
    // get a count of all rows
    let all_rows_count = $('tbody tr').length;


    // set total active listeners 
    let active_count = all_rows_count - hidden 
    
    $('#active').text('Active: ' + active_count);
    $('#disconnected').text('Inactive: ' + hidden );



    if(!toggleCookie){

        console.log('No cookie found. Setting toggleCookie to 0');
        createCookie('toggle', '0', 365 );
        $('#toggle').text('Hide ' + hidden + ' connections.')
        // return;
    
    }else{
        
        // if toggleCookie is 0, hide them
        if(toggleCookie == 0){
            
            console.log('Hiding ' + hidden + ' connections because toggleCookie is ' + toggleCookie);
            
            $('tbody tr:not(.connected)').hide();
            $('#toggle').text('Show all ' + all_rows_count +  ' connections').addClass('hidden');
            
        }else{
            
            console.log( 'toggleCookie = ' + toggleCookie + ', so we are displaying the Hide text');
            // update toggle to show row count, and do default display of all rows
            $('#toggle:not(.hidden)').text('Hide ' + hidden + ' disconnected listeners')
        }

    }



    // move the connected class rows to the top of the table
    var connected_rows = $('table>tbody>tr').get();
    $.each(connected_rows, function( id, row ){
        if( $(row).hasClass('connected')){
            $(row).prependTo('tbody')
        }
    });

    // turn off un-connected from view
    $('#toggle').click(function(){

        let state = readCookie("toggle");

        state = state == 0 ? 1 : 0;
        
        setCookie("toggle" , state , 365 );

        console.log( 'State of Cookie:' + state );


        if($(this).hasClass('hidden')) {

            // update the cookie to 1
            // setCookie("toggle", '1', 365); 
            // show disconnected rows
            $('tbody tr:not(.connected)').show();
            // update link text
            $('#toggle').text('Hide ' + hidden + ' disconnected listeners').removeClass('hidden');
            
            // return;
        
        } else {

            // effect hide of not connected rows
            $('tbody tr:not(.connected)').hide();
            
            console.log('showing all ' + all_rows_count + ' connections');
            $('#toggle').text('Show all ' + all_rows_count +  ' connections').addClass('hidden');
            

        }
    });

    /** Lookup IP click listener */
    
    // Send our results from geoIPLookup to update the listener table 

    // city: "Santa Clara"
    // continent_code : "NA"
    // continent_name : "North America"
    // country_code : "US"
    // country_name : "United States"
    // ip : "198.235.24.184"
    // latitude : 37.39630889892578
    // location : {geoname_id: 5393015, capital: 'Washington D.C.', languages: Array(1), country_flag: 'https://assets.ipstack.com/flags/us.svg', country_flag_emoji: '🇺🇸', …}
    // longitude : -121.9614028930664
    // region_code : "CA"
    // region_name : "California"
    // type : "ipv4"
    // zip : "95054"

    $('.lookup').click(function(){

        var $i              = $(this).closest('tr').attr('data-hostname'); // ip address
        var pretty_country  = $(this).closest('tr').attr('data-pretty-country');
        var $rowId          = $(this).closest('tr').attr('id');

        console.log( "i:" + $i )

        $.post("today.php", {
            dataType: "json",
            data: $i
        }, function( results ) {

            var lookupData = $.parseJSON( results );
            
            if( lookupData.hasOwnProperty('error')){
                
                // console.log( lookupData.error['info'] );
                // Print out the error message.
                $('#notice').text( lookupData.error['info'] ).css('color', 'red').css('font-size','13px')
                $('.lookup').css('cursor','default')

            }else{

                // Update the city cell in the row
                $('#' + $rowId + ' td:nth-child(2)').text( lookupData.city );
    
                // update the state cell in the row
                $('#' + $rowId + ' td:nth-child(3)').text( lookupData.state);
            }




        } );
        
        
    });
    
    

            

    // reload page every 30 seconds.
    setTimeout(function() {
        
        location.reload();

    }, 30000);








// Cookies functions
function createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else var expires = "";               

    document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}
// erase a cookie
function eraseCookie(name) {
    createCookie(name, "", -1);
}
// set a cookie, same as createCookie but used for clarity.
function setCookie(name, value, days) {
    createCookie(name, value, days);
}

}); 


</script>

</body>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>

</html>