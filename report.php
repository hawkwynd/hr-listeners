
<?php 
/**
 * Listeners Reports page
 */
require 'include/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: text/html; charset=ISO-8859-1");

// variables from functions
 $mObj  = current_month_count();
 $since = listeners_since(); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawkwynd Radio Analytics</title>
    
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

<style>
    caption{
        caption-side: top;
        text-align: center;
        color:#114878;
    }

    .table  .thead-dark th{ padding: 8px;}
</style>
</head>
<body>

<div class="container">
    
    <div class="row header">
        <div class="col" id="toggle">
            <a target="_blank" href="http://www.hawkwynd.com">www.hawkwynd.com</a>
        </div>
        <div class="col text-center"><h2>H.A.L. Analytics</h2></div>
        <div class="col text-right">v1.0</div>
    </div>

<!-- Top 10 listeners and top 10 visits -->
<div class="row">
    <div class="col">
        <?php echo top_10(); ?>
    </div>
    <div class="col">
        <?php echo analytics(); ?>
    </div>
</div>

<!-- Top Current Month US Listeners and top 10 by country -->

<div class="row">
    <div class="col">
        <?php echo top_10_by_country(); ?>
    </div>
    <div class="col">
        <?php echo top_US_listeners(); ?>
    </div>
</div>

<!-- Last 72 hrs -->
<div class="row">
    <div class="col">
        <?php echo last72(); ?>
    </div>
</div>

<!-- Jarvis plays data -->

<div class="row">

    <div class="col">
        <?php echo last_hour_plays(); ?>
    </div>
    <div class="col">
        <?php echo top_jarvis_plays(); ?>
    </div>
</div>



<div class="row">
    <div class="col text-center mt-2">
        <h4>Inquring minds want to know, that's why.</h4>
    </div>
</div>

</div><!--container-->


<script>
$(document).ready(function(){

    // createCookie("toggle","all",1 ); // to create new cookie
    console.log('%cHAL Report is active.', 'color:lightgreen');
    
});


</script>

</body>
</html>