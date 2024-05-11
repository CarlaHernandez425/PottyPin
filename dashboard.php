<!-- 
    Carla Hernandez 
    API Endpoints for PottyPin
    October 29, 2023 
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PottyPin API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src='https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.js'></script>
    <link href='https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.css' rel='stylesheet' />
    <link href='css/pottystyles.css?v=2' rel='stylesheet' />

</head>
<body>
<?php include 'navbar.php'; ?>
<div class="centered">
    <h2>Welcome to Pottypin</h2>
</div>

<?php
    session_start();
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    if (isset($_POST['lat']) && isset($_POST['lng'])) {
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        // Save the latitude and longitude to session variables
        $_SESSION['lat'] = $lat;
        $_SESSION['lng'] = $lng;

    //     echo "Location saved to session: Latitude - $lat, Longitude - $lng";
    // } elseif (isset($_SESSION['lat']) && isset($_SESSION['lng'])) {
    //     echo "Your current location - Latitude: " . $_SESSION['lat'] . ", Longitude: " . $_SESSION['lng'];
    // } else {
    //     echo "No data received.";
    }
?>

<div id="map"></div>

<div class="center-button">
    <p>With PottyPin, you can:</p>
    <button onclick="window.location.href='results.php'">Search Bathrooms in the Area</button>
    <p>OR Plan Your Bathroom Breaks on a Specified Location</p>
    <!-- Search Bar for searching by address -->
    <form action="search_results.php" method="get" style="margin-top: 20px;">
        <input type="text" name="address" placeholder="Enter an address to search" required>
        <button type="submit">Search</button>
    </form>
    <p>OR Add Your Favorite Bathroom in the PottyPin Database!</p>
    <!-- Instructions for adding a bathroom to the database -->
    <p>Enter an address, and we'll handle getting its details and adding it to our database.</p>
    <form action="add.php" method="post" style="margin-top: 20px;">
        <input type="text" name="address" placeholder="Enter an address to add" required>
        <button type="submit">Add Bathroom</button>
    </form>
</div>

<?php include 'footer.php'; ?>

<script>
    var map;

    function initializeMap(lat, lng) {
        map = new maplibregl.Map({
            container: 'map', // container id
            style: 'https://api.maptiler.com/maps/bright-v2/style.json?key=yIFC37lpVhEBM5HG2OUY',
            center: [lng, lat],
            zoom: 15 // more detailed zoom
        });

        // Add a marker to the map at the given coordinates
        new maplibregl.Marker()
            .setLngLat([lng, lat])
            .addTo(map);
    }

    // Retrieve PHP session variables safely
    var lat = <?= isset($_SESSION['lat']) ? json_encode($_SESSION['lat']) : '34.1633'; ?>;
    var lng = <?= isset($_SESSION['lng']) ? json_encode($_SESSION['lng']) : '-119.0430'; ?>;

    // Initialize the map with either the session values or default values
    document.addEventListener("DOMContentLoaded", function() {
        initializeMap(parseFloat(lat), parseFloat(lng));
    });
</script>

<script>
    // Check if the browser supports geolocation
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            // Get the latitude and longitude
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;

            // Send the data to the server
            $.ajax({
                url: 'save_location.php',
                method: 'POST',
                data: {
                    lat: lat,
                    lng: lng
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(error) {
                    console.error("Error saving location:", error);
                }
            });
        }, function(error) {
            console.error("Error fetching geolocation:", error.message);
        });
    } else {
        console.log("Geolocation is not supported by this browser.");
    }
</script>

</body>
</html>
