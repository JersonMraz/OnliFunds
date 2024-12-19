<?php
    session_start();
    include("connection.php");

    if(!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    $idno = $_SESSION['id'];

    $query = "SELECT email, firstname, lastname, profile_pic, CONCAT(firstname, ' ', lastname) AS fullname, CONCAT(SUBSTRING(firstname, 1, 1), SUBSTRING(lastname, 1, 1)) AS default_pic FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "<script>alert('No Users Found.')</script>";
        exit();
    }

    $project_id = isset($_GET['proj_id']) ? intval($_GET['proj_id']) : 0;

    if ($project_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM projects WHERE proj_id = ?");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $project = $result->fetch_assoc();
        } else {
            echo "Project not found.";
            exit;
        }
    } else {
        echo "Invalid project ID.";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $imagePath = $project['project_photo'];

        if (!empty($_FILES['image']['name'])) {
            $targetDirectory = "../images/";  // Ensure this directory exists and is writable
            $targetFile = $targetDirectory . basename($_FILES["image"]["name"]);

            // Sanitize file name and check file type (e.g., only allow image files)
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];  // Add more types as needed

            if (in_array($imageFileType, $allowedTypes)) {
                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $imagePath = basename($_FILES["image"]["name"]); // Store the new image path
                } else {
                    echo "Sorry, there was an error uploading your image.";
                    exit();
                }
            } else {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                exit();
            }   
        }

        echo "Image path to be saved: " . $imagePath . "<br>";

        $_SESSION['project_img'] = $imagePath;
        $_SESSION['video_url'] = $_POST['video-url'];
        header('Location: edit3.php?proj_id='. $project_id); // Redirect to the next page
        exit();
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
	<title>OnliFunds</title>
	<style>
		.image-container.cover {
		width: 100%;
        position: relative;
        }

        .image-container.cover img {
            width: 100%;
            height: 300px;
        }
	</style>
</head>
<body>
    <!-- Navbar for Logged-in Users -->
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
                <span class="logged-in-user-name"><?php echo htmlspecialchars($user['fullname']) ?></span>
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="../images/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="logged-in-profile-img">
                <?php else: ?>
                    <div class="author-icon"><?php echo htmlspecialchars($user['default_pic']) ?></div> <!-- Pa fix ko ani sa front-end di ma show ang default -->
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

            // Close the dropdown if clicked outside
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
            <h1>Project Details</h1>
        </div>
    </section>

    <section class="create-container" >
        <div class="content1" style="display: flex; flex-direction:column;">
            <div class="steps" style="display: flex; flex-direction:column;">
                <div class="step">
                    <div class="step-content">
                        <div class="step-icon">
                            <div class="step-pic" style="background-image: url('../images/step1.png');"></div>
                            <span class="step-number step-number-1"><a href="../html/edit1.html" style="text-decoration:none;"><img src="../images/check.svg" alt="Check"></a></span>
                                    <div class="line line-1"></div> <!-- Line under step 1 -->
                        </div>
                        <div class="step-text">
                            <span class="text text-1">Get Started</span>
                        </div>
                    </div>

                </div>

                <div class="step">
                    <div class="step-content">
                        <div class="step-icon">
                            <div class="step-pic" style="background-image: url('../images/step2.png');"></div>
                            <span class="step-number step-number-1"><img src="../images/pen.png" alt="Pen"></span>
                            <div class="line line-2"></div> <!-- Line under step 2 -->
                        </div>
                        <div class="step-text">
                            <span class="text text-2">Resources</span>
                            <span class="description description-1">Add your photos and videos.</span>
                        </div>
                    </div>
                    
                </div>

                <div class="step">
                    <div class="step-content">
                        <div class="step-icon">
                            <div class="step-pic" style="background-image: url('../images/step3.png');"></div>
                            <span class="step-number step-number-3">3</span>
                                    <div class="line line-1"></div> <!-- Line under step 3 -->
                        </div>
                        <div class="step-text">
                            <span class="text text-3">Story</span>
                        </div>
                    </div>

                </div>

                <div class="step">
                    <div class="step-content">
                        <div class="step-icon">
                            <div class="step-pic" style="background-image: url('../images/step4.png');"></div>
                            <span class="step-number step-number-4">4</span>
                        </div>
                        <div class="step-text">
                            <span class="text text-4">Payment Methods</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <form action="" method="post" enctype="multipart/form-data">
                <h2>Fundraiser resources</h2>
                <h3>Upload project photos</h3>
                <p>You can upload several photos in one go.</p>

                <br><br><p>Drag or Upload your photo here</p>
                <?php if (!empty($project['project_photo'])): ?>
                    <div class="current-image">
                        <img src="../images/<?php echo htmlspecialchars($project['project_photo']); ?>" alt="Current Project Photo" width="100">
                        <p>Current Image</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="file-input" />
                <br>
                <div class="video-url">
                    <br>
                    <h3>
                    Video URL
                    </h3>
                    <p>
                    The inclusion of a personalized video can help your project raise money. We support links from GDrive and Youtube.
                    </p>
                    <input placeholder="http://" name="video-url" type="text" value="<?php echo htmlspecialchars($project['video_url']) ?>"/>
                </div>

                <button class="continue-btn" type="submit">Continue</button>
            </form>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const uploadedImagesContainer = document.getElementById('uploaded-images');
                const uploadBox = document.querySelector('.upload-box');

                // Prepopulate with 4 images
                const prepopulatedImages = [
                    "../images/pet1.png",
                    "../images/pet1.png",
                    "../images/pet2.png",
                    "../images/pet3.png"
                ];

                prepopulatedImages.forEach((src, index) => {
                    const imageContainer = document.createElement('div');
                    imageContainer.classList.add('image-container');

                    // Assign the 'cover' class to the first image
                    if (index === 0) {
                        imageContainer.classList.add('cover');
                    }

                    const img = document.createElement('img');
                    img.src = src;
                    imageContainer.appendChild(img);

                    const deleteBtn = document.createElement('div');
                    deleteBtn.classList.add('delete-btn');
                    deleteBtn.innerText = 'Delete';
                    deleteBtn.addEventListener('click', function (event) {
                        event.stopPropagation();
                        const wasCover = imageContainer.classList.contains('cover');
                        imageContainer.remove();

                        // If the deleted image had the 'cover' class, reassign it to the next image
                        if (wasCover) {
                            const firstImageContainer = uploadedImagesContainer.querySelector('.image-container');
                            if (firstImageContainer) {
                                firstImageContainer.classList.add('cover');
                            }
                        }

                        // Reposition the upload box if no images are left
                        if (uploadedImagesContainer.children.length === 0) {
                            uploadedImagesContainer.appendChild(uploadBox);
                        }
                    });

                    imageContainer.appendChild(deleteBtn);

                    // Add click-to-edit functionality for the image
                    img.addEventListener('click', function () {
                        const fileInput = document.createElement('input');
                        fileInput.type = 'file';
                        fileInput.accept = 'image/*';
                        fileInput.style.display = 'none';
                        fileInput.addEventListener('change', function (event) {
                            const newFile = event.target.files[0];
                            const newReader = new FileReader();
                            newReader.onload = function (e) {
                                img.src = e.target.result;
                            };
                            newReader.readAsDataURL(newFile);
                        });
                        document.body.appendChild(fileInput);
                        fileInput.click();
                        document.body.removeChild(fileInput);
                    });

                    uploadedImagesContainer.appendChild(imageContainer);
                });

                // Ensure the upload box stays last
                uploadedImagesContainer.appendChild(uploadBox);

                // Reset file input after changes
                document.getElementById('file-input').addEventListener('change', function (event) {
                    const files = event.target.files;

                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            const imageContainer = document.createElement('div');
                            imageContainer.classList.add('image-container');

                            const img = document.createElement('img');
                            img.src = e.target.result;
                            imageContainer.appendChild(img);

                            const deleteBtn = document.createElement('div');
                            deleteBtn.classList.add('delete-btn');
                            deleteBtn.innerText = 'Delete';
                            deleteBtn.addEventListener('click', function (event) {
                                event.stopPropagation();
                                imageContainer.remove();

                                // Reposition upload box if no images left
                                if (uploadedImagesContainer.children.length === 0) {
                                    uploadedImagesContainer.appendChild(uploadBox);
                                }
                            });

                            imageContainer.appendChild(deleteBtn);

                            uploadedImagesContainer.appendChild(imageContainer);
                            uploadedImagesContainer.appendChild(uploadBox);
                        };

                        reader.readAsDataURL(file);
                    }

                    event.target.value = ''; // Reset the file input
                });
            });
        </script>

    </section>

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
