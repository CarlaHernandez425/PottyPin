<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/pottystyles.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            document.getElementById("password").oninput = validatePassword;
            document.getElementById("confirm_password").oninput = validatePassword;

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const isValidPassword = validatePassword();  // Validate password matching right before attempting to submit
                const isValidUsername = await checkUsername();  // Ensure username is checked asynchronously
                const isValidEmail = await checkEmail();  // Ensure email is checked asynchronously

                if (isValidPassword && isValidUsername && isValidEmail) {
                    form.submit();  // Only submit the form if all validations are passed
                } else {
                    alert('Please correct the errors before submitting.');
                }
            });
        });

        function validatePassword() {
            var password = document.getElementById("password");
            var confirmPassword = document.getElementById("confirm_password");
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
                //confirmPassword.reportValidity();  // To trigger the browser default validation message
                return false;
            } else {
                confirmPassword.setCustomValidity('');
                return true;
            }
        }

        async function checkUsername() {
            var username = document.getElementById('username').value;
            let isValid = true;  // Assume valid unless server tells us otherwise
            await $.ajax({
                url: 'check_username.php',
                type: 'POST',
                data: {username: username},
                success: function(response) {
                    if (response === "taken") {
                        alert('Username already taken. Try another.');
                        document.getElementById('username').value = username + Math.floor(Math.random() * 100);
                        isValid = false;
                    }
                }
            });
            return isValid;
        }

        async function checkEmail() {
            var email = document.getElementById('email').value;
            let isValid = true;  // Assume valid unless server tells us otherwise
            await $.ajax({
                url: 'check_email.php',
                type: 'POST',
                data: {email: email},
                success: function(response) {
                    if (response === "registered") {
                        alert('Email already registered.');
                        document.getElementById('email').value = '';
                        isValid = false;
                    }
                }
            });
            return isValid;
        }
    </script>
</head>
<body>
    <div class="bg">
        <div id="login_banner">
            <h2>Create a PottyPin Account</h2>
        </div>
        <div class="form-container">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="register.php" method="post" onsubmit="validatePassword();">
                            <div class="row mb-3">
                                <label for="firstname" class="col-sm-3 col-form-label">First Name</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                                </div>
                            </div><br>
                            <div class="row mb-3">
                                <label for="lastname" class="col-sm-3 col-form-label">Last Name</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                                </div>
                            </div><br>
                            <div class="row mb-3">
                                <label for="username" class="col-sm-3 col-form-label">Username</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div><br>
                            <div class="row mb-3">
                                <label for="email" class="col-sm-3 col-form-label">Email</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div><br>
                            <div class="row mb-3">
                                <label for="password" class="col-sm-3 col-form-label">Password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="password" name="password" required oninput="validatePassword()">
                                </div>
                            </div><br>
                            <div class="row mb-3">
                                <label for="confirm_password" class="col-sm-3 col-form-label">Re-enter Password</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required oninput="validatePassword()">
                                </div>
                            </div><br>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Register</button>
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

