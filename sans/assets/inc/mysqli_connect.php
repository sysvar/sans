<?php
    // Defined as constants so that they can't be changed
    DEFINE ('DB_USER', 'sans');
    DEFINE ('DB_PASSWORD', 'sans');
    DEFINE ('DB_HOST', '127.0.0.1');
    DEFINE ('DB_NAME', 'sans');

    // $dbc will contain a resource link to the database
    // @ keeps the error from showing in the browser

    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
    OR die('Could not connect to MySQL: ' .
    mysqli_connect_error());

    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);
?>