<!-- 
    API Endpoints for PottyPin
    October 29, 2023 
-->

<?php
    session_start();
    include 'functions.php';
    include 'globals.php';
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);

    require 'db.php';

    $address = $_GET['address'];

    // Get coordinates from the address
    $coordinates = getLatLong($address);
    // Store latitude and longitude in session variables
    $_SESSION['lat'] = $lat = $coordinates['latitude'];
    $_SESSION['lng'] = $lng = $coordinates['longitude'];
    
    $combinedResults = [];

    foreach ($types as $type) {
        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?&location={$lat}%2C{$lng}&radius={$radius}&type={$type}&key={$apiKey}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            die("CURL Error: " . curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if ($data['status'] == 'OK') {
            // Merge the results of this request with the master array
            $combinedResults = array_merge($combinedResults, $data['results']);
        } else {
            // echo "Error for {$type}: " . $data['status'] . "<br>";
        }
    } 

    usort($combinedResults, function($a, $b) use ($lat, $lng) {
        $distanceA = calculateDistance($lat, $lng, $a['geometry']['location']['lat'], $a['geometry']['location']['lng']);
        $distanceB = calculateDistance($lat, $lng, $b['geometry']['location']['lat'], $b['geometry']['location']['lng']);
    
        return $distanceA <=> $distanceB; // PHP 7.0+ spaceship operator for comparison
    });

    // Extracting all the place IDs from Google's results
    $placeIds = array_column($combinedResults, 'place_id');

    // Creating a question mark placeholder for each ID
    $placeholders = implode(',', array_fill(0, count($placeIds), '?'));

    // Prepare the statement
    $stmt = $con->prepare("SELECT * FROM kubetas WHERE place_id IN ($placeholders)");

    // Binding the parameters
    $types = str_repeat('s', count($placeIds));
    $stmt->bind_param($types, ...$placeIds);

    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetching records from kubetas based on place_ids
    $dbResults = $result->fetch_all(MYSQLI_ASSOC);

    // Merging db results with combinedResults
    foreach ($combinedResults as $index => $place) {
        $place['place_id'] = trim($place['place_id']);
        $combinedResults[$index] = $place;

        foreach ($dbResults as $dbResult) {
            if ($place['place_id'] == $dbResult['place_id']) {
                $combinedResults[$index] = array_merge($combinedResults[$index], $dbResult);
                break;
            }
        }
    }

    $stmt->close();

?>

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
    <link href='css/pottystyles.css?v=3' rel='stylesheet' />

</head>
<body>
 
<?php include 'navbar.php'; ?> 

<div class="centered">
    <h2>Welcome to Pottypin</h2>
</div>

<div id="map"></div>

<script>
    var map;

    function initializeMap(lat, lng) {
    map = new maplibregl.Map({
        container: 'map',
        style: 'https://api.maptiler.com/maps/bright-v2/style.json?key=YOUR_API',
        center: [lng, lat],
        zoom: 15
    });

    // Assuming 'locations' contains your places
    var locations = <?= json_encode($combinedResults); ?>;
    locations.forEach(function(place) {
        // Create a DOM element for the marker
        var el = document.createElement('div');
        el.className = 'marker';  // Optionally set a class for CSS styling
        el.style.backgroundImage = 'url(img/toilet2.png)'; // Set path to the icon image
        el.style.width = '25px';  // Set the size of the icon
        el.style.height = '25px';
        el.style.backgroundSize = '100%'; // Ensure the icon covers the element size

        new maplibregl.Marker()
            .setLngLat([lng, lat])
            .addTo(map);

        new maplibregl.Marker(el) // Pass the custom element to the marker
            .setLngLat([place.geometry.location.lng, place.geometry.location.lat])
            .setPopup(new maplibregl.Popup({ offset: 25 }) // Add popups
            .setText(place.name + ' - ' + place.vicinity)) // Display name and vicinity
            .addTo(map);
    });
}

    var lat = <?= json_encode($lat); ?>;
    var lng = <?= json_encode($lng); ?>;

    document.addEventListener("DOMContentLoaded", function() {
        initializeMap(parseFloat(lat), parseFloat(lng));
    });
</script>

<div class="centered">
    <div id="legend" class="legend">
        <b>Legend</b>
        <div class="legend-item">
            <div class="legend-icon"><img src="img/wheelchair.png" alt="Accessible Icon"></div>
            <div class="legend-text">Wheelchair Accessible</div>
        </div>
        <div class="legend-item">
            <div class="legend-icon"><img src="img/family.png" alt="Family Room"></div>
            <div class="legend-text">Family Room</div>
        </div>
        <div class="legend-item">
            <div class="legend-icon"><img src="img/gender-neutral.png" alt="Gender Neutral"></div>
            <div class="legend-text">Gender Neutral</div>
        </div>   
        <div class="legend-item">
            <div class="legend-icon"><img src="img/key.png" alt="Needs Key"></div>
            <div class="legend-text">Needs Key</div>
        </div>
        <div class="legend-item">
            <div class="legend-icon"><img src="img/coin.png" alt="Needs Coin"></div>
            <div class="legend-text">Needs Coin</div>
        </div>     
    </div>
</div>

<?php include "results-table.php"; ?>

<hr>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.collapsible-row').forEach(row => {
            row.addEventListener('click', function() {
                const group = this.getAttribute('data-group');
                document.querySelectorAll(`.collapsible-content[data-group="${group}"]`).forEach(content => {
                    content.style.display = content.style.display === 'none' ? 'table-row' : 'none';
                });
            });
        });
    });
</script>

<script>
    var map;
    function initializeMap(lat, lng) {
        map = new maplibregl.Map({
            container: 'map',
            style: 'https://api.maptiler.com/maps/bright-v2/style.json?key=YOUR_API',
            center: [lng, lat],
            zoom: 15
        });
        var locations = <?= json_encode($combinedResults); ?>;
        locations.forEach(function(place) {
            var el = document.createElement('div');
            el.className = 'marker';
            el.style.backgroundImage = 'url(img/toilet2.png)';
            el.style.width = '25px';
            el.style.height = '25px';
            el.style.backgroundSize = '100%';

            new maplibregl.Marker(el)
                .setLngLat([place.geometry.location.lng, place.geometry.location.lat])
                .setPopup(new maplibregl.Popup({ offset: 25 }).setText(place.name + ' - ' + place.vicinity))
                .addTo(map);
        });
    }

    var lat = <?= json_encode($lat); ?>;
    var lng = <?= json_encode($lng); ?>;
    document.addEventListener("DOMContentLoaded", function() {
        initializeMap(parseFloat(lat), parseFloat(lng));
    });
</script>

<script>
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;

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