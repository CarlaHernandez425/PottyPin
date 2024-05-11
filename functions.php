<?php

function getLatLong($address) {
    require 'globals.php';

    // URL encode the address for the API request
    $address = urlencode($address);

    // Google Maps Geocoding API URL with your API key
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

    // Make the HTTP request to Google Maps API
    $response = file_get_contents($url);
    $json = json_decode($response, true);

    // Check if the request was successful and data was found
    if ($json['status'] == 'OK') {
        // Latitude and longitude are located in the results array
        $latitude =  $json['results'][0]['geometry']['location']['lat'];
        $longitude = $json['results'][0]['geometry']['location']['lng'];
        $place_id =  $json['results'][0]['place_id'];
        $formatted_address = $json['results'][0]['formatted_address'];
        // Return latitude and longitude as an array
        return array('latitude' => $latitude, 'longitude' => $longitude, 'place_id' => $place_id, 'formatted_address' => $formatted_address);
    } else {
        // Return null or a default value in case of failure
        return null;
    }
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $radiusOfEarth = 6371000; // Radius of Earth in meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $radiusOfEarth * $c;

    return $distance; // Distance in meters
}

function getGooglePlaceId($address) {
    
    // URL encode the address for the API request
    $address = urlencode($address);
    
    // Build the Geocoding API request URL
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

    // Send the GET request
    $response = file_get_contents($url);
    $data = json_decode($response, true); // Decode the JSON response

    // Check if the request was successful and results are found
    if ($data['status'] == 'OK' && isset($data['results'][0]['place_id'])) {
        // Return the place_id of the first result
        return $data['results'][0]['place_id'];
    } else {
        // Log error or handle exceptions if needed
        return null; // Return null or handle error as needed
    }
}
