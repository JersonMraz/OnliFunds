<?php
    session_start();
    include('connection.php');
    
    $loginStatus = '';
    $user = null;
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $password = mysqli_real_escape_string($conn, $_POST["password"]);

        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if($result) {
            $user = mysqli_fetch_assoc($result);

            if($user && $password === $user['password']) {
                $loginStatus = 'success';
                $_SESSION['id'] = $user['id'];
            } else {
                $loginStatus = 'failed';
            }
        } else {
            $loginStatus = 'failed';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | OnliFunds</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Centered Navbar -->
    <nav class="navar">
        <div class="navar-logo" >
			<a href="landing.html" style="text-decoration:none;">
				<img src="../images/logo.png" alt="OnliFunds Logo" class="logo-img">
			</a>
            <div class="logo-text">
                <h1><a href="landing.html" style="text-decoration:none; color:black;">OnliFunds</a></h1>
                <p>Empower Your Ideas</p>
            </div>
        </div>
    </nav>

    <!-- Background section with overlay and login content -->
    <div class="background-section">
        <!-- Overlay covering only the background image -->
        <div class="overly"></div>
        
        <!-- Container for the entire login page -->
        <div class="login-container">
            <!-- Left section with the message and overlay -->
            <div class="login-info">
                <div class="info-content">
                    <h2>Join Us in Making a Difference</h2>
                    <p>Create and support meaningful campaigns that make a difference in your community and beyond.</p>
                </div>
				<div style="margin-left: 20px;">
				<img src="../images/illustration.png" alt="Illustration" class="illustration">
				</div>
			</div>

            <!-- Right section with the login form -->
            <div class="login-form">
                <h1>Welcome Back!</h1>
                <h2>Log in</h2>
                <form action="login.php" method="POST">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required>
                    
				<label for="password">Password</label>
				<div class="password-container">
					<input type="password" id="password" name="password" required>
					<span class="toggle-password"><i class="fa fa-eye"></i></span>
					<span id="password-mask" class="password-mask" style="display: none;"></span>
				</div>



                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>

                    <p class="terms">
                        By continuing, you agree to the <a href="#">Terms of use</a> and <a href="#">Privacy Policy</a>.
                    </p>

                    <button type="submit" class="login-button">Log in</button>

                    <div class="extra-links">
						<div style="margin-bottom: 15px; text-align: center;">
                        <a href="#" style="font-weight:bold;">Forgot your password</a>
						</div>
						<div style="text-align: center;">
                        <span>Don’t have an account? <a href="register.php" style="font-weight:bold;">Sign up</a></span>
						</div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    const togglePassword = document.querySelector('.toggle-password');
    const passwordField = document.querySelector('#password');
    const passwordMask = document.querySelector('#password-mask');

    togglePassword.addEventListener('click', function () {
        if (passwordMask.style.display === 'none') {
            // Show password as text in the mask
            passwordMask.style.display = 'inline';
            passwordMask.innerText = passwordField.value;
            passwordField.style.color = 'transparent';
            togglePassword.querySelector('i').classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            // Hide the mask and show password as dots
            passwordMask.style.display = 'none';
            passwordField.style.color = 'inherit';
            togglePassword.querySelector('i').classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Update mask when typing in the password field
    passwordField.addEventListener('input', function () {
        if (passwordMask.style.display !== 'none') {
            passwordMask.innerText = passwordField.value;
        }
    }); 

    <?php if ($loginStatus): ?>
        if('<?php echo $loginStatus; ?>' === 'success') {
            Swal.fire({
                title: 'Logged In',
                text: 'You have successfully logged in.',
                icon: 'success',
                focusConfirm: false,
                confirmButtonText: 'OK',
                timer: 1200,
                timerProgressBar: true,
                didOpen: () => {
                    document.activeElement.blur();
                    const confirmButton = Swal.getConfirmButton();

                    confirmButton.style.border = '2px solid #d3d3d3';
                    confirmButton.style.borderRadius = '10px';
                    confirmButton.style.backgroundColor = '#d3d3d3';
                    confirmButton.style.color = '#ffffff';
                },
                willClose: () => {
                    window.location.href = "lanAfter.php";
                }
            });
        } else if('<?php echo $loginStatus; ?>' === 'failed') {
            Swal.fire({
                title: 'Oops...',
                text: 'Please check your credentials.',
                icon: 'error',
                confirmBtnText: 'Try Again'
            });
        }
    <?php endif; ?>  
</script>
</html>
