<?php
require 'db.php'; // Database connection
require 'functions.php'; // Contains getGooglePlaceId and other necessary functions

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address'])) {
    $address = $_POST['address'];
    $placeId = getLatLong($address);

    if ($placeId) {
        $place_id = $placeId['place_id'];
        echo '<br>';
        $name = $placeId['name'];
        echo '<br>';
        $formatted_address = $placeId['formatted_address'];
        echo '<br>'; 
        $latitude = $placeId['latitude'];
        echo '<br>'; 
        $longitude = $placeId['longitude'];

        // Prepare SQL for insertion
        $sql = "INSERT INTO kubetas (place_id, name, formatted_address, lat, lng) VALUES (?, ?, ?, ?, ?)";
        echo "Prepared statement: " . $sql . "\n"; // Display the SQL template

        // Create a string for debugging with actual values to visualize the complete query
        $debugQuery = sprintf("Debug Query: INSERT INTO kubetas (place_id, name, formatted_address, lat, lng) VALUES ('%s', '%s', '%s', %f, %f)",
                            $place_id, $name, $formatted_address, $latitude, $longitude);
        echo $debugQuery . "\n"; // Display the query with actual values for debugging

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssd', $place_id, $name, $formatted_address, $latitude, $longitude);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);


        // Redirect or notify user
        echo "Bathroom added successfully!";
    } else {
        echo "Failed to get the place ID for " . $address ;
    }
} else {
    echo "No address provided.";
}
