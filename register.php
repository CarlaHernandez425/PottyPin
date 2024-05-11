<?php
/**
 *  5/8/24 - Added hashed password functionality, username field and data validation
 *  
 * 
 * 
 */

session_start();
include 'debug.php';
require_once 'db.php'; 

$firstname  = $_POST['firstname'];
$lastname   = $_POST['lastname'];
$email      = $_POST['email'];
$username   = $_POST['username'];
$password   = $_POST['password']; 

// Hashing the password with default algorithm (bcrypt)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// SQL query to insert the user data
$query = "INSERT INTO logins (firstname, lastname, email, username, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $con->prepare($query);
if ($stmt === false) {
    die('MySQL prepare error: ' . $con->error);
}

$stmt->bind_param("sssss", $firstname, $lastname, $email, $username, $hashed_password);
$executeResult = $stmt->execute();

if ($executeResult) {
    // Set session variables to log the user in
    $_SESSION['islogged']   = true;
    $_SESSION['email']      = $email; // Adjust based on your session management
    $_SESSION['fullname']   = $firstname . ' ' . $lastname;
    $_SESSION['username']   = $username;

    // Redirect to the index page
    header("Location: index.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$con->close();
