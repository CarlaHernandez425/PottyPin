<?php

// include 'debug.php';
require 'db.php';

header('Content-Type: application/json');

// Check API key
$apiKey = "YOUR_API";
$api = $_GET['api'];

if ( $api != $apiKey ) {
    die ( json_encode ( ['error' => 'PottyPin API Key needed'] ) );
}

if ( isset ( $_GET['place_id'] ) ) {
    $place_id = $_GET['place_id'];
    fetchData ( $con, $place_id );
} else {
    echo json_encode ( ['error' => 'place_id does not exist.'] );
}

// Google API
$googleApi = "YOUR_API";

mysqli_close ( $con );

function fetchData ( $connection, $place_id ) {
    $stmt = $connection->prepare ( "SELECT * FROM `kubetas` WHERE place_id = ?" );
    $stmt->bind_param ( "s", $place_id );  // "s" specifies the variable type => 'string'

    if ( !$stmt->execute() ) {
        die ( json_encode ( ['error' => 'Failed to execute query: ' . $stmt->error] ) );
    }

    $result = $stmt->get_result();
    $data = $result->fetch_all ( MYSQLI_ASSOC );
    echo json_encode ( $data );

    $stmt->close();
}

?>
