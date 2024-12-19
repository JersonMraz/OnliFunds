<?php
    include("connection.php");
    
    $registerStatus = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $firstname = mysqli_real_escape_string($conn, $_POST["first-name"]);
        $lastname = mysqli_real_escape_string($conn, $_POST["last-name"]);
        $password = mysqli_real_escape_string($conn, $_POST["password"]);
        $confirmpass = mysqli_real_escape_string($conn, $_POST["confirm-password"]);

        $query = "INSERT INTO users(email, password,firstname, lastname, isAdmin) VALUES(?, ?, ?, ?, 0)";
        
        if($password <> $confirmpass) {
            $registerStatus = 'error';
        }
        elseif($conn->connect_error){
            die('Connection failed : ' .$conn->connect_error);
        }
        else {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $email, $password, $firstname, $lastname);
            $stmt->execute();
            $registerStatus = 'success';
            $stmt->close();
            $conn->close();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | OnliFunds</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Centered Navbar -->
    <nav class="navar">
        <div class="navar-logo">
            <img src="../images/logo.png" alt="OnliFunds Logo" class="logo-img">
            <div class="logo-text">
                <h1>OnliFunds</h1>
                <p>Empower Your Ideas</p>
            </div>
        </div>
    </nav>

    <!-- Background section with overlay and registration content -->
    <div class="background-section">
        <div class="overly"></div>
        
        <div class="register-container">
            <div class="register-info">
                <div class="info-content">
                    <h2>Join Us in Making a Difference</h2>
                    <p>Create and support meaningful campaigns that make a difference in your community and beyond.</p>
                </div>
                <div style="margin-left: 20px;">
                    <img src="../images/illustration.png" alt="Illustration" class="illustration">
                </div>
            </div>

            <div class="register-form">
                <h1>Sign up now</h1>
                <form id="register-form" action="register.php" method="POST">
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1;">
                            <label for="first-name">First name</label>
                            <input type="text" id="first-name" name="first-name" required>
                        </div>
                        <div style="flex: 1;">
                            <label for="last-name">Last name</label>
                            <input type="text" id="last-name" name="last-name" required>
                        </div>
                    </div>

                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Password</label>
                    <div class="password-container" style="margin-bottom:30px;">
                        <input type="password" id="password" name="password" required>
                        <span class="toggle-password"><i class="fa fa-eye"></i></span>
                        <span id="password-mask" class="password-mask" style="display: none;"></span>
                    </div>

                    <label for="confirm-password">Confirm</label>
                    <div class="password-container">
                        <input type="password" id="confirm-password" name="confirm-password" required>
                        <span class="toggle-password"><i class="fa fa-eye"></i></span>
                        <span id="confirm-password-mask" class="password-mask" style="display: none;"></span>
                    </div>

                    <p class="terms" style="text-align:center;">
                        Use 8 or more characters with a mix of letters, numbers & symbols.
                    </p>
                    
                    <div id="error-message" style="color: red; text-align: center;"></div>

                    <button type="submit" name="submit" class="register-button">Sign up</button>

                    <div class="extra-links">
                        <div style="text-align: center;">
                            <span>Already have an account? <a href="login.php" style="font-weight:bold;">Log in</a></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
const togglePasswordIcons = document.querySelectorAll('.toggle-password');
togglePasswordIcons.forEach(togglePassword => {
    togglePassword.addEventListener('click', function () {
        const passwordField = this.previousElementSibling;
        const passwordMask = this.nextElementSibling;
        if (passwordMask.style.display === 'none') {
            passwordMask.style.display = 'inline';
            passwordMask.innerText = passwordField.value;
            passwordField.style.color = 'transparent';
            this.querySelector('i').classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordMask.style.display = 'none';
            passwordField.style.color = 'inherit';
            this.querySelector('i').classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// Update mask when typing in the password fields
const passwordFields = document.querySelectorAll('input[type="password"]');
passwordFields.forEach(passwordField => {
    passwordField.addEventListener('input', function () {
        const passwordMask = this.nextElementSibling.nextElementSibling;
        if (passwordMask.style.display !== 'none') {
            passwordMask.innerText = this.value;
        }
    });
});

/*
// Form validation
document.getElementById('register-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const errorMessage = document.getElementById('error-message');
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/;

    if (!passwordRegex.test(password)) {
        errorMessage.textContent = 'Password must be at least 8 characters long and include a mix of letters, numbers, and symbols.';
    } else if (password !== confirmPassword) {
        errorMessage.textContent = 'Passwords do not match.';
    } else {
        errorMessage.textContent = '';
        this.submit(); 
    }
});
*/
<?php if ($registerStatus): ?>
        if('<?php echo $registerStatus; ?>' === 'success') {
            Swal.fire({
                title: 'Perfect!',
                text: 'You registered successfully',
                icon: 'success',
                confirmButtonText: 'OK',
            });
        } else if('<?php echo $registerStatus; ?>' === 'error') {
            Swal.fire({
                title: 'Oops...',
                text: 'Password does not match!',
                icon: 'error',
                confirmBtnText: 'Try Again'
            });
        }
    <?php endif; ?>   
</script>

</html>