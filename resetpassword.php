<?php
session_start();
include 'db.php'; 

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: index.php');
    exit;
}

$message = ''; // Message to display to the user

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password == $confirm_password) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Prepare SQL to update the user's password
        $sql = "UPDATE logins SET password = ? WHERE username = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('ss', $hashed_password, $_SESSION['username']);

        if ($stmt->execute()) {
            $message = 'Password successfully updated!';
        } else {
            $message = 'Failed to update password.';
        }
    } else {
        $message = 'Passwords do not match.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <?php if($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
    <form method="post" action="resetpassword.php">
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</body>
</html>
