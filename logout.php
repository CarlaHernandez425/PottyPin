<?php
    session_start(); // Start the session
    //session_unset(); // Unset all of the session variables.
    session_destroy(); // Destroy the session.

    header("Location: index.php?login=loggedout"); // Redirect to login page with status
    exit();
?>
e