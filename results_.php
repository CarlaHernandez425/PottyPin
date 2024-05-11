<?php
// results.php
    session_start();

    // include 'debug.php';
    require 'db.php';

    // Check if lat and lng are set in the session
    if (!isset($_SESSION['lat']) || !isset($_SESSION['lng'])) {
        die("Location not set in session.");
    }

    $lat    = $_SESSION['lat'];
    $lng    = $_SESSION['lng'];
    $radius = 100000;
    
    $apiKey = "YOUR_API";

    $types = ['restaurant', 'cafe', 'gas_station', 'drugstore', 'supermarket'];

    function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $radiusOfEarth = 6371000; // Radius of Earth in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
    
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
        $distance = $radiusOfEarth * $c;
    
        return $distance; // Distance in meters
    }

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
            echo "Error for {$type}: " . $data['status'] . "<br>";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .center-content {
            text-align: center;
        }
        thead > tr > th {
            text-decoration: bold;
            color: white;
            background-color: black;
            text-align: center;
            width: 3rem;
        }
        tbody > tr:nth-child(odd) {
            background-color: #89CFF0; /* bebe blue */
        }
        thead th {
            word-wrap: break-word;
            max-width: 100px;  /* You can adjust this value as needed */
        }
    </style>
</head>

<body>
    <a href="index.php"><h4>Home</h4></a>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Restroom</th>
                <th>Address</th>
                <!-- <th>Place ID</th> -->
                <th>Availability</th>
                <th>Code</th>
                <th>Gender Neutral</th>
                <th>Family Room</th>
                <th>Needs Key</th>
                <th>Needs Coin</th>
                <th>Add Info</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($combinedResults as $place): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $place['name'] ?></td>
                <td>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($place['vicinity']) ?>" target="_blank">
                        <?= $place['vicinity'] ?>
                    </a>
                </td>
                <!-- <td><?= $place['place_id'] ?></td> -->
                <td class="text-center">
                    <?php 
                        if (isset($place['opening_hours'])) {
                            echo $place['opening_hours']['open_now'] ? "<span style='color:green'>Open</span>" : "<span style='color:red'>Closed</span>";
                        } else {
                            echo "Unknown";
                        }
                    ?>
                </td>
                <td class="text-center">
                    <?= $place['bathroom_code'] ? $place['bathroom_code'] : 'No Data' ?>
                </td>
                <td class="text-center">
                    <?= !empty($place['has_gender_neutral']) ? '<span class="fa fa-check" style="color:green;"></span>' : '<span style="display:none;"></span>' ?>
                </td>
                <td class="text-center">
                    <?= !empty($place['has_family_room']) ? '<span class="fa fa-check" style="color:green;"></span>' : '<span style="display:none;"></span>' ?>
                </td>
                <td class="text-center">
                    <?= !empty($place['needs_key']) ? '<span class="fa fa-check" style="color:green;"></span>' : '<span style="display:none;"></span>' ?>
                </td>
                <td class="text-center">
                    <?= !empty($place['needs_coin']) ? '<span class="fa fa-check" style="color:green;"></span>' : '<span style="display:none;"></span>' ?>
                </td>
                <td class="text-center">
                    <a href="edit.php?place_id=<?= $place['place_id'] ?>" class="btn btn-primary">Edit</a>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
    