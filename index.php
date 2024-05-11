<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">


    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        .bg {
            background-image: url('img/PottyPin_LogInBackground.jpg');
            height: 100vh; /* Full viewport height */
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative; /* Needed for absolute positioning of children */
        }
        #login_banner {
            position: absolute;
            top: 20%; /* Adjust as needed */
            width: 100%; /* Cover the full width of bg */
            text-align: center; /* Center the text horizontally */
            color: #654321;
            font-family: 'Roboto', sans-serif;
        }
        .form-container {
            position: absolute;
            top: 30%; /* Adjust based on your design preference */
            width: 100%; /* Form container takes full width of bg */
            display: flex;
            justify-content: center; /* Center form horizontally */
        }
        .card {
            background-color: white; /* Solid white background */
            opacity: 1; /* Fully opaque */
            padding: 2rem; /* Padding around the form for aesthetics */
            border-radius: 0.5rem; /* Slight rounding of corners */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Subtle shadow for depth */
            width: auto; /* Adjust width as necessary */
            color: #654321;
            font-family: 'Roboto', sans-serif;
        }
        .btn-pill {
            background-color: #9dc183;
            border-color: #9dc183;
            border-radius: 50px;
        }
        .btn-pill:hover {
            background-color: #654321;
            border-color: #654321;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="bg">
            <div id="login_banner">
                <h2>Login to PottyPin</h2>
            </div>

            <div class="form-container">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="login.php" method="post">
                                <div class="row mb-3">
                                    <label for="email" class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="password" class="col-sm-3 col-form-label">Password</label>
                                    <div class='col-sm-9'>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-pill">Login</button>
                                    <button type="button" onclick="window.location.href='signup.php'" class="btn btn-secondary btn-pill">Sign Up</button>
                                </div>
                            </form>
                            <div>
                                <hr>
                                <a href="dashboard.php"> Or browse PottyPin as a Guest </a>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        
        <!-- Place for messages -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div id="message" class="mt-3"></div>
            </div>
        </div>
    </div>

    <?php include 'footer.html' ?>

    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const loginStatus = urlParams.get('login');
            const messageElement = document.getElementById('message');

            if (loginStatus === 'success') {
                messageElement.innerHTML = '<div class="alert alert-success" role="alert">Logged in Successfully!</div>';
            } else if (loginStatus === 'failed') {
                messageElement.innerHTML = '<div class="alert alert-danger" role="alert">Log-in Failed.</div>';
            } else if (loginStatus === 'loggedout') {
                messageElement.innerHTML = '<div class="alert alert-info" role="alert">Logged-out Successfully.</div>';
            }
        }
    </script>

    <script>
        window.onload = function() {
            if ("geolocation" in navigator && !localStorage.getItem('locationSaved')) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;

                    $.ajax({
                        url: 'save_location.php',
                        method: 'POST',
                        data: {
                            lat: lat,
                            lng: lng
                        },
                        success: function(response) {
                            console.log('Location saved:', response);
                            localStorage.setItem('locationSaved', 'true'); // Set the flag
                            window.location.reload(); // Optionally reload if needed, otherwise update UI here
                        },
                        error: function(error) {
                            console.error("Error saving location:", error);
                        }
                    });
                }, function(error) {
                    console.error("Error fetching geolocation:", error.message);
                });
            } else {
                console.log("Geolocation is not supported by this browser or location is already saved.");
            }
        }
    </script>

    <script>
    function logoutUser() {
        localStorage.removeItem('locationSaved');  // Remove the specific item
        //localStorage.clear();  // Uncomment this line if you want to clear all localStorage data
        window.location.href = 'logout.php';  // Redirect to the logout page
    }
    </script>

    <script type="text/javascript">
        window.onload = function() {
            // Check URL parameters for specific errors and alert the user
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error === 'incorrect') {
                alert('Incorrect Email and Password Combination.');
            } else if (error === 'unregistered') {
                alert('Email is not registered. Click OK to register.');
                // Optionally redirect to the signup page
                window.location.href = 'signup.php';
            }
        };
    </script>   

</body>
</html>
