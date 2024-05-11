<?php

session_start();
include 'debug.php';
require_once 'db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the submitted email and password
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Create the SQL query using prepared statements
    $stmt = $con->prepare("SELECT * FROM logins WHERE email = ? LIMIT 1");
    if (!$stmt) {
        // Handle prepare error
        echo "Error in prepare statement: " . $con->error;
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the hashed password with the password entered
        if (password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['email']    = $email;  // Set session after validating user
            $_SESSION['islogged'] = TRUE;
            $_SESSION['fullname'] = $user['firstname'] . ' ' . $user['lastname'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");  // Redirect to a logged-in page
            exit();
        } else {
            // Password does not match
            header("Location: index.php?error=incorrect");
            exit();
        }
    } else {
        // No user found with that email address
        header("Location: index.php?error=unregistered");
        exit();
    }
}
