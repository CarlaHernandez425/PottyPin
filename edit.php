<?php
session_start();
require 'db.php';
include 'globals.php';

$placeData = [
    'place_id' => '',
    'formatted_address' => '',
    'lat' => '',
    'lng' => '',
    'contributor' => '',
    'bathroom_code' => '',
    'wheelchair' => 0,
    'has_family_room' => 0,
    'has_gender_neutral' => 0,
    'needs_key' => 0,
    'needs_coin' => 0
];

if (isset($_GET['place_id'])) {
    $place_id = trim($_GET['place_id']);
    $stmt = mysqli_prepare($con, "SELECT * FROM kubetas WHERE place_id = ?");
    mysqli_stmt_bind_param($stmt, 's', $place_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $placeData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // If not found in the kubetas database, fetch from Google Places API
    if (!$placeData) {
        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&key={$apiKey}";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] == 'OK') {
            $placeDetails = $data['result'];
            $placeData = [
                'place_id' => $placeDetails['place_id'],
                'name' => $placeDetails['name'],
                'formatted_address' => $placeDetails['formatted_address'],
                'lat' => $placeDetails['geometry']['location']['lat'],
                'lng' => $placeDetails['geometry']['location']['lng'],
                // ... Add other fields as needed
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $place_id           = trim($_POST['place_id']);
    $name               = trim($_POST['name']);
    $formatted_address  = trim($_POST['formatted_address']);
    $lat                = trim($_POST['lat']);
    $lng                = trim($_POST['lng']);
    $contributor        = $_SESSION['username'];
    $bathroom_code      = trim($_POST['bathroom_code']);
    $wheelchair         = $_POST['wheelchair'] ? 1 : 0;
    $has_family_room    = $_POST['has_family_room'] ? 1 : 0;
    $has_gender_neutral = $_POST['has_gender_neutral'] ? 1 : 0;
    $needs_key          = $_POST['needs_key'] ? 1 : 0;
    $needs_coin         = $_POST['needs_coin'] ? 1 : 0;
    $score              = $_POST['score'];
    $review             = $_POST['review'];
    $date               = date("m-Y");
    
    // Check if a record with the same place_id already exists using mysqli
    $stmt = mysqli_prepare($con, "SELECT * FROM kubetas WHERE place_id = ?");
    mysqli_stmt_bind_param($stmt, 's', $place_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existingPlace = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($existingPlace) {
        // Update record using mysqli
        $stmt = mysqli_prepare($con, "UPDATE kubetas SET name = ?, formatted_address = ?, lat = ?, lng = ?, contributor = ?, bathroom_code = ?, wheelchair = ?, has_family_room = ?, has_gender_neutral = ?, needs_key = ?, needs_coin = ? WHERE place_id = ?");
        mysqli_stmt_bind_param($stmt, 'sssssssiiiis', $name, $formatted_address, $lat, $lng, $contributor, $bathroom_code, $wheelchair, $has_family_room, $has_gender_neutral, $needs_key, $needs_coin, $place_id);
        
        $stmt2 = mysqli_prepare($con, "UPDATE reviews SET score = ? WHERE place_id = ? AND  contributor = ?");
        mysqli_stmt_bind_param($stmt2, 'dss', $score, $place_id, $contributor);
        
        $stmt3 = mysqli_prepare($con, "INSERT INTO codes (place_id, contributor, code, date) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt3, 'ssss', $place_id, $contributor, $bathroom_code, $date);

    } else {
        // Insert new record using mysqli
        $stmt = mysqli_prepare($con, "INSERT INTO kubetas (place_id, name, formatted_address, lat, lng, contributor, wheelchair, bathroom_code, has_family_room, has_gender_neutral, needs_key, needs_coin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssssssiiiis', $place_id, $name, $formatted_address, $lat, $lng, $contributor, $wheelchair, $bathroom_code, $has_family_room, $has_gender_neutral, $needs_key, $needs_coin);
        
        $stmt2 = mysqli_prepare($con, "INSERT INTO reviews (place_id, contributor, review, score, date) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, 'sssds', $place_id, $contributor, $review, $score, $date);
        
        $stmt3 = mysqli_prepare($con, "INSERT INTO codes (place_id, contributor, code, date) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt3, 'ssss', $place_id, $contributor, $bathroom_code, $date);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
    mysqli_stmt_execute($stmt3);
    mysqli_stmt_close($stmt3);
    $con->close();

    header('Location: results.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Place Details</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/pottystyles.css" rel="stylesheet">
</head>
<body>
    <div class="bg">
        <div class="container mt-4">
            <div class="form-container">
                <div class="card">
                    <div class="card-body">
                        <form action="edit.php" method="post">
                            <h2 class="text-center mb-4"><?php echo htmlspecialchars($placeData['name']); ?></h2>
                            <input type="hidden" name="place_id" value="<?php echo htmlspecialchars($placeData['place_id']); ?>">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($placeData['name']); ?>">
                            <input type="hidden" name="formatted_address" value="<?php echo htmlspecialchars($placeData['formatted_address']); ?>">
                            <input type="hidden" name="lat" value="<?php echo htmlspecialchars($placeData['lat']); ?>">
                            <input type="hidden" name="lng" value="<?php echo htmlspecialchars($placeData['lng']); ?>">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">

                            <!-- Group items for better structure and responsiveness -->
                            <div class="form-group">
                                <label><b>Address:</b></label><br>
                                <?php echo htmlspecialchars($placeData['formatted_address']); ?>
                            </div>

                            <!-- <div class="form-group">
                                <label>Latitude:</label>
                                <p>< ?php echo htmlspecialchars($placeData['lat']); ?></p>
                            </div>

                            <div class="form-group">
                                <label>Longitude:</label>
                                <p>< ?php echo htmlspecialchars($placeData['lng']); ?></p>
                            </div> -->
                            <br>
                            <div class="form-group">
                                <label><b>Contributor:</b></label><br>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </div>
                            <br>
                            <div class="form-group">
                                <label><b>Bathroom Code:</b></label><br>
                                <input type="text" class="form-control" name="bathroom_code" value="<?php echo htmlspecialchars($placeData['bathroom_code']); ?>">
                            </div>
                            <br>
                            <!-- Accessibility options -->
                            <div class="form-group">
                                <label>Wheelchair Accessible:</label>
                                <select class="form-control" name="wheelchair">
                                    <option value="1" <?php echo $placeData['wheelchair'] === "1" ? 'selected' : ''; ?>>Yes</option>
                                    <option value="0" <?php echo $placeData['wheelchair'] === "0" ? 'selected' : ''; ?>>No</option>
                                    <option value="" <?php echo is_null($placeData['wheelchair']) ? 'selected' : ''; ?>>No Data</option>
                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label>Has Gender Neutral:</label>
                                <select class="form-control" name="has_gender_neutral">
                                    <option value="1" <?php echo $placeData['has_gender_neutral'] === "1" ? 'selected' : ''; ?>>Yes</option>
                                    <option value="0" <?php echo $placeData['has_gender_neutral'] === "0" ? 'selected' : ''; ?>>No</option>
                                    <option value="" <?php echo is_null($placeData['has_gender_neutral']) ? 'selected' : ''; ?>>No Data</option>
                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label>Has Family Room:</label>
                                <select class="form-control" name="has_family_room">
                                    <option value="1" <?php echo $placeData['has_family_room'] === "1" ? 'selected' : ''; ?>>Yes</option>
                                    <option value="0" <?php echo $placeData['has_family_room'] === "0" ? 'selected' : ''; ?>>No</option>
                                    <option value="" <?php echo is_null($placeData['has_family_room']) ? 'selected' : ''; ?>>No Data</option>
                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label>Needs Key:</label>
                                <select class="form-control" name="needs_key">
                                    <option value="1" <?php echo $placeData['needs_key'] === "1" ? 'selected' : ''; ?>>Yes</option>
                                    <option value="0" <?php echo $placeData['needs_key'] === "0" ? 'selected' : ''; ?>>No</option>
                                    <option value="" <?php echo is_null($placeData['needs_key']) ? 'selected' : ''; ?>>No Data</option>
                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label>Needs Coin:</label>
                                <select class="form-control" name="needs_coin">
                                    <option value="1" <?php echo $placeData['needs_coin'] === "1" ? 'selected' : ''; ?>>Yes</option>
                                    <option value="0" <?php echo $placeData['needs_coin'] === "0" ? 'selected' : ''; ?>>No</option>
                                    <option value="" <?php echo is_null($placeData['needs_coin']) ? 'selected' : ''; ?>>No Data</option>
                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label>Score:</label>
                                <select class="form-control" name="score">
                                    <?php for ($i = 5; $i >= 1; $i--) {
                                        $selected = $placeData['rating'] == $i ? 'selected' : '';
                                        echo "<option value='$i' $selected>$i</option>";
                                    } ?>
                                </select>
                            </div>
                            <br>

                            <?php
                                // Prepare the SQL statement
                                $stmt = mysqli_prepare($con, "SELECT review FROM reviews WHERE place_id = ? AND contributor = ?");
                                mysqli_stmt_bind_param($stmt, 'ss', $place_id, $contributor);

                                // Execute the query
                                mysqli_stmt_execute($stmt);

                                // Bind the result variable
                                mysqli_stmt_bind_result($stmt, $review);

                                // Fetch the result
                                $previousReview = '';
                                if (mysqli_stmt_fetch($stmt)) {
                                    $previousReview = $review; // Store the review if it exists
                                }

                                mysqli_stmt_close($stmt);
                            ?>

                            <div class="form-group">
                                <label>Write a review:</label>
                                <textarea class="form-control" name="review" rows="4"><?php echo htmlspecialchars($previousReview); ?></textarea>
                            </div>

                            <br>
                            <div class="d-grid gap-2 mt-3">
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <input type="button" class="btn btn-secondary" value="Back" onclick="window.history.back();">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
