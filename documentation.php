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
</head>
<body>

<div class="centered">
    <h2>PottyPin Demo & API Documentation</h2>
</div>

<?php
    // index.php
    session_start();
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    if (isset($_POST['lat']) && isset($_POST['lng'])) {
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        // Save the latitude and longitude to session variables
        $_SESSION['lat'] = $lat;
        $_SESSION['lng'] = $lng;

        echo "Location saved to session: Latitude - $lat, Longitude - $lng";
    } elseif (isset($_SESSION['lat']) && isset($_SESSION['lng'])) {
        echo "Your current location - Latitude: " . $_SESSION['lat'] . ", Longitude: " . $_SESSION['lng'];
    } else {
        echo "No data received.";
    }
?>

<div>
    <button onclick="window.location.href='results.php'">Search Bathrooms</button>
</div>
    
<div>
    <p>URI: https://pottypin.appsbycarla.com/api.php?api={YOUR_API_KEY}&endpoint={ENDPOINT_OPTIONS}</p>
    <p>
        <b>ENDPOINT OPTIONS:</b><br>
        <u>place_id</u> - cross referenced to Google Places API Decoded JSON ['place_id']<br>
    </p>
    <p>
        <b>Technologies Used:</b><br>
        <u>PHP</u> - A popular server-side scripting language.
        <ul>
            <li><b>Session:</b> PHP's built-in session handling capabilities were used to temporarily store the user's latitude and longitude across pages.</li>
        </ul>
        <u>JavaScript</u> - A client-side scripting language.
        <ul>
            <li><b>AJAX (with jQuery):</b> Used to send asynchronous HTTP requests to the server without reloading the page.</li>
            <li><b>Geolocation API:</b> A web API provided by modern browsers to access the geographical location of a device.</li>
        </ul>
        <u>Google Places API</u> - A service by Google that provides information about local businesses and other points of interest.<br>
        <u>HTML & CSS</u> - Used for structuring and styling the web pages.<br>
    </p>

    <p>
        <b>Geolocation Algorithm:</b><br>
        <ol>
            <li><b>Initialize the session and database connection:</b> The PHP session is started to allow for storing and retrieval of session variables, and a database connection is initiated using 'db.php'.</li>
            <li><b>Check for Location Data in Session:</b> 
                <ol>
                    <li>Verify if the geolocation data (<code>lat</code> and <code>lng</code>) is set in the session.</li>
                    <li>If not available, the script terminates with a message "Location not set in session."</li>
                </ol>
            </li>

            <li><b>Retrieve Nearby Places:</b>
                <ol>
                    <li>Use the stored <code>lat</code> and <code>lng</code> values from the session to query the Google Places API.</li>
                    <li>Fetch nearby places of interest (e.g., restaurants, gas stations, drugstores) within a specific radius.</li>
                    <li>Combine results from each type of place into a single array called <code>combinedResults</code>.</li>
                </ol>
            </li>

            <li><b>Sort Combined Results:</b>
                <ol>
                    <li>Use the <code>calculateDistance</code> function to measure the distance between the user's location and each place's location.</li>
                    <li>Sort the <code>combinedResults</code> array based on distance, with the nearest places appearing first.</li>
                </ol>
            </li>

            <li><b>Fetch Data from Local Database:</b>
                <ol>
                    <li>Extract place IDs from Google's results and then query the local database (table named 'kubetas') to fetch additional details about these places.</li>
                    <li>Merge the local database results with the <code>combinedResults</code> from the Google Places API.</li>
                </ol>
            </li>

            <li><b>Edit Place Data:</b> 
                <ol>
                    <li>On accessing <code>edit.php</code>, it first connects to the database and checks for a given place ID.</li>
                    <li>If the place ID is found in the local database, fetch and display its details. Otherwise, retrieve the place's details from the Google Places API.</li>
                    <li>Upon form submission, check if the place already exists in the database.</li>
                    <li>If it exists, update the record; otherwise, insert a new record into the 'kubetas' table with the provided details.</li>
                </ol>
            </li>

            <li><b>Redirect:</b> After updating or inserting the place details in <code>edit.php</code>, redirect the user back to the <code>results.php</code> page.</li>
        </ol>
    </p>

    <p>
        <b>Tech Stack:</b>
        <ul>
            <li><b>PHP:</b> Server-side scripting language used to manage sessions, connect to the database, and perform operations.</li>
            <li><b>MySQL:</b> The relational database used to store and retrieve place details.</li>
            <li><b>Google Places API:</b> External API used to fetch nearby places based on geolocation data.</li>
            <li><b>cURL:</b> A PHP library used to make HTTP requests, specifically to fetch data from the Google Places API.</li>
        </ul>
    </p>
</div>

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
