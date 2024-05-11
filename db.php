<?php

$host      = "127.0.0.1:3306";
$user      = "u803325201_admin"; 
$password  = "P@55w0rdN1T@B@"; 
$db_name   = "u803325201_pottypin"; 
$con       = mysqli_connect ( $host, $user, $password, $db_name);

if ( !$con ) {
    die ( "Database connection error: " . mysqli_connect_error() );
}
