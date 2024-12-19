<?php
    session_start();
    include("connection.php");

    if(!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    $idno = $_SESSION['id'];
    $updateStatus = '';

    //Fetch current user details
    $query = "SELECT email, phoneno, firstname, lastname, profile_pic, barangay, CONCAT(firstname, ' ', lastname) AS fullname, CONCAT(SUBSTRING(firstname, 1, 1), SUBSTRING(lastname, 1, 1)) AS default_pic FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            // Get file data
            $fileName = $_FILES['profile_pic']['name'];
            $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
            $fileSize = $_FILES['profile_pic']['size'];
            $fileType = $_FILES['profile_pic']['type'];
    
            // Allowed file types
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                echo "Invalid file type. Please upload a JPEG, PNG, or GIF file.";
                exit();
            }
            $newFileName =  basename($fileName);
    
            // Update the user's profile picture in the database with the new filename
            $updateQuery = "UPDATE users SET profile_pic = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $newFileName, $idno);
            $stmt->execute();
        }
    

        // Get the form data
        $firstname = $_POST['first-name'];
        $lastname = $_POST['last-name'];
        $email = $_POST['email'];
        $phoneno = $_POST['phone-number'];
        $barangay = $_POST['barangay'];
        $currentpass = $_POST['current-password'];
        $newpassword = $_POST['new-password'];
        $confirmpass = $_POST['confirm-password'];

        if (isset($_POST['remove_profile_pic']) && $_POST['remove_profile_pic'] === '1') {
            $updatePicQuery = "UPDATE users SET profile_pic = NULL WHERE id = ?";
            $stmt = $conn->prepare($updatePicQuery);
            $stmt->bind_param("i", $idno);
            $stmt->execute();
        }

        // Fetch the current user data from the database
        $fetchQuery = "SELECT firstname, lastname, email, phoneno, barangay FROM users WHERE id = ?";
        $stmt = $conn->prepare($fetchQuery);
        $stmt->bind_param("i", $idno);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentUser = $result->fetch_assoc();

        if (!empty($newpassword) || !empty($confirmpass)) {
        if ($newpassword !== $confirmpass) {
            // Passwords don't match
            $updateStatus = "NotMatch";
        } else {
            // Passwords match, update password
            $updateQuery = "UPDATE users SET firstname = ?, lastname = ?, email = ?, phoneno = ?, barangay = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssssssi", $firstname, $lastname, $email, $phoneno, $barangay, $confirmpass, $idno);
            $stmt->execute();
            $updateStatus = 'success';
        }
        } else {
            // No password change, update other fields
            $updateQuery = "UPDATE users SET firstname = ?, lastname = ?, email = ?, phoneno = ?, barangay = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssssi", $firstname, $lastname, $email, $phoneno, $barangay, $idno);
            $stmt->execute();
            $updateStatus = 'success';
        }

        // Check if there are any changes
        if (
            $firstname === $currentUser['firstname'] &&
            $lastname === $currentUser['lastname'] &&
            $email === $currentUser['email'] &&
            $phoneno === $currentUser['phoneno'] &&
            $barangay === $currentUser['barangay']
        ) {
            // No changes detected
            $updateStatus = 'error';
        } else if ($newpassword !== $confirmpass) {
            //Wrong Password
            $updateStatus = "NotMatch";
        } else {
            $stmt->execute();
            $updateStatus = 'success';
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <link href="../css/main.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Ranchers&family=Rubik+Glitch&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<title>OnliFunds</title>
	<style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-section {
            padding: 40px;
            margin-top: 20px;
        }
        .form-section h2 {
            font-size: 28px;
            margin-bottom: 20px;
			text-align:center;
        }
        .form-section h3 {
            font-size: 20px;
            color: #00796b;
            margin-bottom: 10px;
        }
        .form-g {
            margin-bottom: 20px;
        }
        .form-g label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-g input, .form-g select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-g .profile-pic {
            display: flex;
            align-items: center;
        }
        .form-g .profile-pic img, .form-g .profile-pic .default-pic {
            height: 80px;
            width: 80px;
            border-radius: 50%;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            color: #fff;
            background-color: #00796b;
        }
        .form-g .profile-pic .actions {
            display: flex;
            flex-direction: column;
        }
        .form-g .profile-pic .actions button {
            background: none;
            border: none;
            color: #00796b;
            cursor: pointer;
            margin-bottom: 5px;
            font-size: 16px;
        }
        .form-g .profile-pic .actions button.remove {
            color: #d32f2f;
        }
        .form-g .change-password {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
			width: 50%;
			background-color: white;
        }
        .form-g .change-password i {
            margin-right: 5px;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .form-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-actions .close-button {
            background-color: #fff;
            border: 1px solid #ddd;
            color: #000;
			width: 45%;
        }
        .form-actions .save-button {
            background-color: #00796b;
            color: #fff;
			width: 45%;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
        }
        .form-row .form-g {
            flex: 1;
            margin-right: 10px;
        }
        .form-row .form-g:last-child {
            margin-right: 0;
        }
	</style>
</head>
<body>
    <nav class="logged-in-navbar">
        <div class="logged-in-navbar-logo">
            <a href="lanAfter.php" style="text-decoration:none;">
                <img src="../images/logo.png" alt="OnliFunds Logo" class="logged-in-logo-img">
            </a>
            <div class="logged-in-logo-text">
                <h1><a href="lanAfter.php" style="text-decoration:none; color:black;">OnliFunds</a></h1>
                <p>Empower Your Ideas</p>
            </div>
        </div>
        <div class="logged-in-nav-links">
            <a href="lanAfter.php" class="logged-in-nav-item">Home</a>
            <a href="aboutA.php" class="logged-in-nav-item">About</a>
            <a href="projectsA.php" class="logged-in-nav-item">Projects</a>
            <a href="create1.php" class="logged-in-nav-item logged-in-start-project-button">Start a Project</a>
            <div class="logged-in-user-profile">
                <span class="logged-in-user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="../images/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="logged-in-profile-img">
                <?php else: ?>
                    <div class="author-icon"><?php echo htmlspecialchars($user['default_pic']) ?></div>
                <?php endif; ?>
                <div class="dropdown-menu">
                    <a href="my-projects.php">My Projects</a>
                    <a href="backed-projects.html">Backed Projects</a>
                    <a href="profile-settings.php">Profile Settings</a>
                    <a href="logout.php">Log Out</a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const userProfile = document.querySelector('.logged-in-user-profile');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            userProfile.addEventListener('click', function () {
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });

            document.addEventListener('click', function (e) {
                if (!userProfile.contains(e.target)) {
                    dropdownMenu.style.display = 'none';
                }
            });
        });
    </script>

    <!-- About Banner Section -->
    <section class="banner1">
        <div class="overlay"></div>
        <div class="banner-content">
            <h1>Profile Settings</h1>
        </div>
    </section>
    <form action="profile-settings.php" method="POST" enctype="multipart/form-data">
        <div class="container">
            <div class="form-section">
                    <h2>Edit user profile</h2>
                    <h3>Personal Details</h3>
                    <div class="form-row">
                        <div class="form-g">
                            <label for="first-name">First name</label>
                            <input type="text" id="first-name" name="first-name" value="<?php echo htmlspecialchars($user['firstname']); ?>">
                        </div>
                        <div class="form-g">
                            <label for="last-name">Last name</label>
                            <input type="text" id="last-name" name="last-name" value="<?php echo htmlspecialchars($user['lastname']); ?>">
                        </div>
                    </div>
                    <div class="form-g">
                        <label>Upload profile picture</label>
                        <div class="profile-pic">
                            <?php if (!empty($user['profile_pic'])): ?>
                                <img src="../images/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" width="100px" height="100px" id="profile-picture">
                            <?php else: ?>
                                <div class="default-pic" id="default-pic"><?php echo htmlspecialchars($user['default_pic']) ?></div>
                            <?php endif; ?>
                            <img id="profile-picture-preview" style="display: none;" width="100px" height="100px">
                            <div class="default-pic" style="display: none;" id="default-pic" width="100px" height="100px"><?php echo htmlspecialchars($user['default_pic']) ?></div>
                            <div class="actions">
                                <button class="change" type="button" onclick="document.getElementById('file-input').click();"><i class="fas fa-plus"></i> Change</button>
                                <button class="remove" type="button" onclick="removeProfilePicture();"><i class="fas fa-trash"></i> Remove</button>
                                <input type="file" id="file-input" style="display: none;" name="profile_pic" onchange="changeProfilePicture(event);">
                            </div>
                        </div>
                    </div>
                    <div class="form-g">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="form-g">
                        <label for="phone-number">Phone number</label>
                        <input type="tel" pattern="[0-9]{11}" id="phone-number" name="phone-number" value="0<?php echo htmlspecialchars($user['phoneno']) ?>">
                    </div>
                    <div class="form-g">
                        <label>Password</label>
                        <div class="change-password" onclick="openChangePasswordModal();"><i class="fas fa-lock"></i> Change Password</div>
                    </div>
                    <h3>Location</h3>
                    <div class="form-g">
                        <label for="country">Country</label>
                        <input type="text" id="country" value="Philippines" readonly>
                    </div>
                    <div class="form-g">
                        <label for="city">City</label>
                        <input type="text" id="city" value="Cebu City" readonly>
                    </div>
                    <div class="form-g">
                        <label for="barangay">Barangay</label>
                        <select id="barangay" name="barangay">
                            <option value="Sapangdaku" <?php if ($user['barangay'] === 'Sapangdaku') echo 'selected'; ?>>Sapangdaku</option>
                            <option value="Apas" <?php if ($user['barangay'] === 'Apas') echo 'selected'; ?>>Apas</option>
                            <option value="Banilad" <?php if ($user['barangay'] === 'Banilad') echo 'selected'; ?>>Banilad</option>
                            <option value="Basak Pardo" <?php if ($user['barangay'] === 'Basak Pardo') echo 'selected'; ?>>Basak Pardo</option>
                            <option value="Basak San Nicolas" <?php if ($user['barangay'] === 'Basak San Nicolas') echo 'selected'; ?>>Basak San Nicolas</option>
                            <option value="Bonbon" <?php if ($user['barangay'] === 'Bonbon') echo 'selected'; ?>>Bonbon</option>
                            <option value="Budlaan" <?php if ($user['barangay'] === 'Budlaan') echo 'selected'; ?>>Budlaan</option>
                            <option value="Buhisan" <?php if ($user['barangay'] === 'Buhisan') echo 'selected'; ?>>Buhisan</option>
                            <option value="Busay" <?php if ($user['barangay'] === 'Busay') echo 'selected'; ?>>Busay</option>
                            <option value="Capitol Site" <?php if ($user['barangay'] === 'Capitol Site') echo 'selected'; ?>>Capitol Site</option>
                            <option value="Carreta" <?php if ($user['barangay'] === 'Carreta') echo 'selected'; ?>>Carreta</option>
                            <option value="Cogon Ramos" <?php if ($user['barangay'] === 'Cogon Ramos') echo 'selected'; ?>>Cogon Ramos</option>
                            <option value="Day-as" <?php if ($user['barangay'] === 'Day-as') echo 'selected'; ?>>Day-as</option>
                            <option value="Duljo" <?php if ($user['barangay'] === 'Duljo') echo 'selected'; ?>>Duljo</option>
                            <option value="Guadalupe" <?php if ($user['barangay'] === 'Guadalupe') echo 'selected'; ?>>Guadalupe</option>
                            <option value="Kalunasan" <?php if ($user['barangay'] === 'Kalunasan') echo 'selected'; ?>>Kalunasan</option>
                            <option value="Kasambagan" <?php if ($user['barangay'] === 'Kasambagan') echo 'selected'; ?>>Kasambagan</option>
                            <option value="Kinasang-an" <?php if ($user['barangay'] === 'Kinasang-an') echo 'selected'; ?>>Kinasang-an</option>
                            <option value="Labangon" <?php if ($user['barangay'] === 'Labangon') echo 'selected'; ?>>Labangon</option>
                            <option value="Lahug" <?php if ($user['barangay'] === 'Lahug') echo 'selected'; ?>>Lahug</option>
                            <option value="Lorega San Miguel" <?php if ($user['barangay'] === 'Lorega San Miguel') echo 'selected'; ?>>Lorega San Miguel</option>
                            <option value="Luz" <?php if ($user['barangay'] === 'Luz') echo 'selected'; ?>>Luz</option>
                            <option value="Mabolo" <?php if ($user['barangay'] === 'Mabolo') echo 'selected'; ?>>Mabolo</option>
                            <option value="Mambaling" <?php if ($user['barangay'] === 'Mambaling') echo 'selected'; ?>>Mambaling</option>
                            <option value="Pahina Central" <?php if ($user['barangay'] === 'Pahina Central') echo 'selected'; ?>>Pahina Central</option>
                            <option value="Pahina San Nicolas" <?php if ($user['barangay'] === 'Pahina San Nicolas') echo 'selected'; ?>>Pahina San Nicolas</option>
                            <option value="Pasil" <?php if ($user['barangay'] === 'Pasil') echo 'selected'; ?>>Pasil</option>
                            <option value="Pit-os" <?php if ($user['barangay'] === 'Pit-os') echo 'selected'; ?>>Pit-os</option>
                            <option value="Poblacion Pardo" <?php if ($user['barangay'] === 'Poblacion Pardo') echo 'selected'; ?>>Poblacion Pardo</option>
                            <option value="Pulangbato" <?php if ($user['barangay'] === 'Pulangbato') echo 'selected'; ?>>Pulangbato</option>
                            <option value="Punta Princesa" <?php if ($user['barangay'] === 'Punta Princesa') echo 'selected'; ?>>Punta Princesa</option>
                            <option value="Quiot" <?php if ($user['barangay'] === 'Quiot') echo 'selected'; ?>>Quiot</option>
                            <option value="Sambag I" <?php if ($user['barangay'] === 'Sambag I') echo 'selected'; ?>>Sambag I</option>
                            <option value="Sambag II" <?php if ($user['barangay'] === 'Sambag II') echo 'selected'; ?>>Sambag II</option>
                            <option value="San Antonio" <?php if ($user['barangay'] === 'San Antonio') echo 'selected'; ?>>San Antonio</option>
                            <option value="San Jose" <?php if ($user['barangay'] === 'San Jose') echo 'selected'; ?>>San Jose</option>
                            <option value="San Nicolas Proper" <?php if ($user['barangay'] === 'San Nicolas Proper') echo 'selected'; ?>>San Nicolas Proper</option>
                            <option value="Santa Cruz" <?php if ($user['barangay'] === 'Santa Cruz') echo 'selected'; ?>>Santa Cruz</option>
                            <option value="Santo Niño" <?php if ($user['barangay'] === 'Santo Niño') echo 'selected'; ?>>Santo Niño</option>
                            <option value="Sawang Calero" <?php if ($user['barangay'] === 'Sawang Calero') echo 'selected'; ?>>Sawang Calero</option>
                            <option value="Sinsin" <?php if ($user['barangay'] === 'Sinsin') echo 'selected'; ?>>Sinsin</option>
                            <option value="Suba" <?php if ($user['barangay'] === 'Suba') echo 'selected'; ?>>Suba</option>
                            <option value="Sudlon I" <?php if ($user['barangay'] === 'Sudlon I') echo 'selected'; ?>>Sudlon I</option>
                            <option value="Sudlon II" <?php if ($user['barangay'] === 'Sudlon II') echo 'selected'; ?>>Sudlon II</option>
                            <option value="T. Padilla" <?php if ($user['barangay'] === 'T. Padilla') echo 'selected'; ?>>T. Padilla</option>
                            <option value="Talamban" <?php if ($user['barangay'] === 'Talamban') echo 'selected'; ?>>Talamban</option>
                            <option value="Taptap" <?php if ($user['barangay'] === 'Taptap') echo 'selected'; ?>>Taptap</option>
                            <option value="Tejero" <?php if ($user['barangay'] === 'Tejero') echo 'selected'; ?>>Tejero</option>
                            <option value="Tinago" <?php if ($user['barangay'] === 'Tinago') echo 'selected'; ?>>Tinago</option>
                            <option value="Tisa" <?php if ($user['barangay'] === 'Tisa') echo 'selected'; ?>>Tisa</option>
                            <option value="Toong" <?php if ($user['barangay'] === 'Toong') echo 'selected'; ?>>Toong</option>
                            <option value="Zapatera" <?php if ($user['barangay'] === 'Zapatera') echo 'selected'; ?>>Zapatera</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button class="close-button" type="button" onclick="closeProfile()">Close</button>
                        <button class="save-button" type="submit">Save</button>
                    </div>
            </div>
        </div>
        <div id="change-password-modal" style="display: none;">
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center;">
                <div style="background: #fff; padding: 20px; border-radius: 4px; width: 300px;">
                    <h3>Change Password</h3>
                    <div class="form-g">
                        <label for="current-password">Current Password</label>
                        <input type="password" id="current-password" name="current-password">
                    </div>
                    <div class="form-g">
                        <label for="new-password">New Password</label>
                        <input type="password" id="new-password" name="new-password">
                    </div>
                    <div class="form-g">
                        <label for="confirm-password">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm-password">
                    </div>
                    <div class="form-actions">
                        <button class="close-button" type="reset" onclick="closeChangePasswordModal();">Cancel</button>
                        <button class="save-button" type="submit">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script>
        <?php if ($updateStatus): ?>
            if('<?php echo $updateStatus; ?>' === 'success') {
                Swal.fire({
                    title: 'Success',
                    text: 'Information updated.',
                    icon: 'success',
                    focusConfirm: false,
                    confirmButtonText: 'OK',
                    didOpen: () => {
                        document.activeElement.blur();
                        const confirmButton = Swal.getConfirmButton();

                        confirmButton.style.border = '2px solid #d3d3d3';
                        confirmButton.style.borderRadius = '10px';
                        confirmButton.style.backgroundColor = '#d3d3d3';
                        confirmButton.style.color = '#ffffff';
                    },
                    willClose: () => {
                        window.location.href = "profile-settings.php";
                    }
                });
            } else if ('<?php echo $updateStatus ?>' === 'NotMatch') {
                Swal.fire({
                    title: 'New password did not match!',
                    icon: 'warning',
                    confirmBtnText: 'OK'
                });
            } else if('<?php echo $updateStatus; ?>' === 'error') {
                Swal.fire({
                    title: 'No changes made.',
                    icon: 'info',
                    confirmBtnText: 'OK'
                });
            }
        <?php endif; ?>  

        function closeProfile() {
            window.location.href = "lanAfter.php";
        }

        function changeProfilePicture(event) {
            // Get the file input element
            const fileInput = event.target;
            const file = fileInput.files[0]; // Get the first selected file

            // If the file is an image, display it as a preview
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // Get the result of the file and set it as the src of the preview image
                    const previewImage = document.getElementById('profile-picture-preview');
                    const currentImage = document.getElementById('profile-picture');

                    // Hide the current profile picture and show the new one
                    currentImage.style.display = 'none';
                    previewImage.style.display = 'block'; // Show the preview image
                    previewImage.src = e.target.result;
                }

                reader.readAsDataURL(file); // Read the file as data URL
            }
        }

        function removeProfilePicture() {
            document.getElementById('profile-picture-preview').style.display = 'none';
            document.getElementById('profile-picture').style.display = 'none';
            document.getElementById('profile-picture').src = '';
            document.getElementById('profile-picture-preview').src = '';
            document.getElementById('default-pic').style.display = 'block';
            document.getElementById('default-pic').style.alignContent = 'center';
            document.getElementById('default-pic').style.textAlign = 'center';
        }

        function openChangePasswordModal() {
            document.getElementById('change-password-modal').style.display = 'flex';
        }

        function closeChangePasswordModal() {
            document.getElementById('change-password-modal').style.display = 'none';
        }
    </script>

    <footer class="footer">
        <div class="footer-content">
            <!-- About Section -->
            <div class="footer-section about" style="text-align: left; padding-right:200px;">
                <h2 class="footer-logo">OnliFunds</h2>
                <p class="footer-description">
                    OnliFunds is a crowdfunding website that lets you raise money for anything that matters to you.
                    From personal causes and events to projects and more. We aim to help people from our community
                    to raised the funds they need.
                </p>
            </div>

            <!-- Links Section -->
            <div class="footer-section links" style="text-align: left; padding-right:200px;">
                <h3 class="footer-heading">Learn More</h3>
                <ul class="footer-links">
                    <li><a href="/about">About</a></li>
                    <li><a href="/#team-members">Team Members</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms And Conditions</a></li>
                </ul>
            </div>

            <!-- Social Media Section -->
            <div class="footer-section social-media" style="text-align: left;">
                <h3 class="footer-heading">Social Medias</h3>
                <ul class="social-icons">
                    <li><a href="#"><img src="../images/facebook.png" alt="Facebook Icon" style="width: 28px;height:auto;"> Facebook</a></li>
                    <li><a href="#"><img src="../images/twitter.png" alt="Twitter Icon" style="width: 28px;height:auto;"> Twitter</a></li>
                    <li><a href="#"><img src="../images/instagram.png" alt="Instagram Icon" style="width: 28px;height:auto;"> Instagram</a></li>
                    <li><a href="#"><img src="../images/linkedin.png" alt="LinkedIn Icon" style="width: 28px;height:auto;"> LinkedIn</a></li>
                </ul>
            </div>
        </div>

        <!-- Copyright Section -->
        <div class="footer-bottom">
            <p>© 2024 Copyright OnliFunds. All Rights Reserved</p>
        </div>
    </footer>
</body>
</html>
