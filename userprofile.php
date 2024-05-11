<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif; /* Consistent font family */
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            width: 350px;
        }

        .account-heading {
            font-size: 24px;
            color: #9dc183; /* Accent color for headings */
            margin-bottom: 20px;
            text-align: center;
        }

        .form-control, .form-control:focus {
            border-color: #9dc183;
            box-shadow: none; /* Removes Bootstrap's default focus shadow */
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #9dc183; /* Main color for buttons */
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background-color: #86a96f; /* Slightly darker on hover */
        }

        .details {
            font-size: 16px;
            color: #666; /* Subtle text color */
            line-height: 1.5; /* Improved readability */
            margin-bottom: 10px;
        }
</style>
</head>
<body>
<div class="container">
    <div class="account-heading">My Account</div>
    <?php
    include 'db.php'; 
    // include 'debug.php';

    session_start(); // Start session management
    $email = $_SESSION['email']; // Use email from session

    // SQL to fetch user data
    $sql = "SELECT firstname, lastname, username, email FROM logins WHERE email = ?";
    $stmt = $con->prepare($sql); 
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo "First Name: " . htmlspecialchars($row["firstname"]) . "<br>";
            echo "Last Name: " . htmlspecialchars($row["lastname"]) . "<br>";
            echo "Username: " . htmlspecialchars($row["username"]) . "<br>";
            echo "Email: " . htmlspecialchars($row["email"]) . "<br>";
        }
    } else {
        echo "You are browsing as Guest.  Create an account now to access other features";
    }
    $con->close();
    ?>

    <button onclick="location.href='dashboard.php'">Go Back</button>
     
    <?php if ($_SESSION['islogged'] == TRUE) { ?>
        <button onclick="location.href='resetpassword.php'">Reset Password</button> 
    <?php } else { ?>
        <button onclick="location.href='signup.php'">Create an Account</button>
    <?php } ?>

</div>
</body>
</html>



