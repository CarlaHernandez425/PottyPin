<?php
    session_start();
?>

<div class="topnav">
    <a href="dashboard.php">Search</a>
    <a href="userprofile.php">My Account</a>
    <a href="documentation.php">Help</a>
    <?php if ($_SESSION['islogged'] == TRUE) {
        echo '<a href="logout.php">Log Out</a>';
    } ?>
    
</div>