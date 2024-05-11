<?php 
session_start();
include 'debug.php';

header('Content-Type: application/json');  // Ensure the output is treated as JSON

if (isset($_POST['lat']) && isset($_POST['lng'])) {
    $_SESSION['lat'] = $_POST['lat'];
    $_SESSION['lng'] = $_POST['lng'];

    echo json_encode(['status' => 'success', 'message' => "Location saved to session: Latitude - {$_POST['lat']}, Longitude - {$_POST['lng']}"]);
} else {
    echo json_encode(['status' => 'error', 'message' => "No data received."]);
}
?>
