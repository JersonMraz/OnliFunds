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

    $selectedCategory = $project['category'];
    $selectedBarangay = $project['barangay'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_SESSION['project_title'] = $_POST['project-title'];
        $_SESSION['project_category'] = $_POST['project-category'];
        $_SESSION['barangay'] = $_POST['barangay'];
        $_SESSION['fund_goal'] = $_POST['fund-goal'];
        $_SESSION['end_date'] = $_POST['end-date'];
        $_SESSION['firstname'] = $_POST['first-name'];
        $_SESSION['lastname'] = $_POST['last-name'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['phone-number'] = $_POST['phone-number'];
        header('Location: edit2.php?proj_id='. $project_id); // Redirect to the next page
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
    <title>OnliFunds</title>
	<style>
		.line-1 {
		 height: 40px;
		 }
		.line-2 {
			height: 20px; /* Height of the vertical line */
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
            <h1>Edit Project</h1>
        </div>
    </section>

    <section class="create-container" >
        <div class="content1" style="display: flex; flex-direction:column;">
            <div class="steps" style="display: flex; flex-direction:column;">
                <div class="step">
                    <div class="step-content">
                        <div class="step-icon">
                            <div class="step-pic" style="background-image: url('../images/step1.png');"></div>
                            <span class="step-number step-number-1"><img src="../images/pen.png" alt="Pen"></span>
                                    <div class="line line-1"></div> <!-- Line under step 1 -->
                        </div>
                        <div class="step-text">
                            <span class="text text-1">Get Started</span>
                            <span class="description description-1">Set essential fundraiser details such as fundraiser title, target, overview, and location.</span>
                        </div>
                    </div>

                </div>

                <div class="step">
                    <div class="step-content">
                        <div class="step-icon">
                            <div class="step-pic" style="background-image: url('../images/step2.png');"></div>
                            <span class="step-number step-number-2">2</span>
                            <div class="line line-2"></div> <!-- Line under step 2 -->
                        </div>
                        <div class="step-text">
                            <span class="text text-2">Resources</span>
                        </div>
                    </div>
                    
                </div>

                <div class="step">
                    <div class="step-content">
                        <div class="step-icon">
                            <div class="step-pic" style="background-image: url('../images/step3.png');"></div>
                            <span class="step-number step-number-3">3</span>
                                    <div class="line line-2"></div> <!-- Line under step 3 -->
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

        <div class="form-container">
            <form action="" method="post">
                <h2>Let's get started</h2>
                <h3>Fundraiser information</h3>
                <label for="project-title">Project title</label>
                <input id="project-title" name="project-title" placeholder="Add title of your project" type="text" value="<?php echo htmlspecialchars($project['proj_title'])?>"/>
                <label for="project-category">Project category</label>
                <select id="project-category" name="project-category">
                    <option value="" <?php echo ($selectedCategory == '') ? 'selected' : ''; ?>>Please Select</option>
                    <option value="Animals" <?php echo ($selectedCategory == 'Animals') ? 'selected' : ''; ?>>Animals</option>
                    <option value="Business" <?php echo ($selectedCategory == 'Business') ? 'selected' : ''; ?>>Business & Startups</option>
                    <option value="Health" <?php echo ($selectedCategory == 'Health') ? 'selected' : ''; ?>>Health</option>
                    <option value="Education" <?php echo ($selectedCategory == 'Education') ? 'selected' : ''; ?>>Education</option>
                    <option value="Nature" <?php echo ($selectedCategory == 'Nature') ? 'selected' : ''; ?>>Nature</option>
                    <option value="Technology" <?php echo ($selectedCategory == 'Technology') ? 'selected' : ''; ?>>Technology</option>
                </select>
                <h3>Fundraiser location</h3>
                <p>Choose the location where you plan to withdraw your funds. We only support those within Cebu City, Philippines.</p>
                <label for="country">Country</label>
                <input id="country" name="country" readonly type="text" value="Philippines"/>
                <label for="city">City</label>
                <input id="city" name="city" readonly type="text" value="Cebu"/>
                <label for="barangay">Barangay</label>
                <select id="barangay" name="barangay">
                    <option>Please Select</option>
                    <option value="Apas" <?php echo ($selectedBarangay == 'Apas') ? 'selected' : ''; ?>>Apas</option>
                    <option value="Banilad" <?php echo ($selectedBarangay == 'Banilad') ? 'selected' : ''; ?>>Banilad</option>
                    <option value="Basak Pardoo" <?php echo ($selectedBarangay == 'Basak Pardo') ? 'selected' : ''; ?>>Basak Pardo</option>
                    <option value="Basak San Nicolas" <?php echo ($selectedBarangay == 'Basak San Nicolas') ? 'selected' : ''; ?>>Basak San Nicolas</option>
                    <option value="Bato" <?php echo ($selectedBarangay == 'Bato') ? 'selected' : ''; ?>>Bato</option>
                    <option value="Bayanihan" <?php echo ($selectedBarangay == 'Bayanihan') ? 'selected' : ''; ?>>Bayanihan</option>
                    <option value="Bojo" <?php echo ($selectedBarangay == 'Bojo') ? 'selected' : ''; ?>>Bojo</option>
                    <option value="Bonbon" <?php echo ($selectedBarangay == 'Bonbon') ? 'selected' : ''; ?>>Bonbon</option>
                    <option value="Camputhaw" <?php echo ($selectedBarangay == 'Camputhaw') ? 'selected' : ''; ?>>Camputhaw</option>
                    <option value="Carreta" <?php echo ($selectedBarangay == 'Carreta') ? 'selected' : ''; ?>>Carreta</option>
                    <option value="Casuntingan" <?php echo ($selectedBarangay == 'Casuntingan') ? 'selected' : ''; ?>>Casuntingan</option>
                    <option value="Cahumayan" <?php echo ($selectedBarangay == 'Cahumayan') ? 'selected' : ''; ?>>Cahumayan</option>
                    <option value="Guba" <?php echo ($selectedBarangay == 'Guba') ? 'selected' : ''; ?>>Guba</option>
                    <option value="Guadalupe" <?php echo ($selectedBarangay == 'Guadalupe') ? 'selected' : ''; ?>>Guadalupe</option>
                    <option value="Karla" <?php echo ($selectedBarangay == 'Karla') ? 'selected' : ''; ?>>Karla</option>
                    <option value="Kawasan" <?php echo ($selectedBarangay == 'Kawasan') ? 'selected' : ''; ?>>Kawasan</option>
                    <option value="Labangon" <?php echo ($selectedBarangay == 'Labangon') ? 'selected' : ''; ?>>Labangon</option>
                    <option value="Lantana" <?php echo ($selectedBarangay == 'Lantana') ? 'selected' : ''; ?>>Lantana</option>
                    <option value="Mambaling" <?php echo ($selectedBarangay == 'Mambaling') ? 'selected' : ''; ?>>Mambaling</option>
                    <option value="Mayan" <?php echo ($selectedBarangay == 'Mayan') ? 'selected' : ''; ?>>Mayan</option>
                    <option value="Pahina Central" <?php echo ($selectedBarangay == 'Pahina Central') ? 'selected' : ''; ?>>Pahina Central</option>
                    <option value="Parian" <?php echo ($selectedBarangay == 'Parian') ? 'selected' : ''; ?>>Parian</option>
                    <option value="Pardo" <?php echo ($selectedBarangay == 'Pardo') ? 'selected' : ''; ?>>Pardo</option>
                    <option value="Pasil" <?php echo ($selectedBarangay == 'Pasil') ? 'selected' : ''; ?>>Pasil</option>
                    <option value="Pulangbato" <?php echo ($selectedBarangay == 'Pulangbato') ? 'selected' : ''; ?>>Pulangbato</option>
                    <option value="Pit-os" <?php echo ($selectedBarangay == 'Pit-os') ? 'selected' : ''; ?>>Pit-os</option>
                    <option value="San Isidro" <?php echo ($selectedBarangay == 'San Isidro') ? 'selected' : ''; ?>>San Isidro</option>
                    <option value="San Jose" <?php echo ($selectedBarangay == 'San Jose') ? 'selected' : ''; ?>>San Jose</option>
                    <option value="San Nicolas" <?php echo ($selectedBarangay == 'San Nicolas') ? 'selected' : ''; ?>>San Nicolas</option>
                    <option value="San Roque" <?php echo ($selectedBarangay == 'San Roque') ? 'selected' : ''; ?>>San Roque</option>
                    <option value="Santo Niño" <?php echo ($selectedBarangay == 'Santo Niño') ? 'selected' : ''; ?>>Santo Niño</option>
                    <option value="Sugbo" <?php echo ($selectedBarangay == 'Sugbo') ? 'selected' : ''; ?>>Sugbo</option>
                    <option value="Sugbo" <?php echo ($selectedBarangay == 'Sapangdaku') ? 'selected' : ''; ?>>Sapangdaku</option>
                    <option value="Talisay" <?php echo ($selectedBarangay == 'Talisay') ? 'selected' : ''; ?>>Talisay</option>
                    <option value="Toledo" <?php echo ($selectedBarangay == 'Toledo') ? 'selected' : ''; ?>>Toledo</option>
                    <option value="Tungol" <?php echo ($selectedBarangay == 'Tungol') ? 'selected' : ''; ?>>Tungol</option>
                </select>
                <h3>Donation information</h3>
                <label for="fund-goal">Fund Goal</label>
                <input id="fund-goal" name="fund-goal" placeholder="Add the goal amount to raise" type="text" value="<?php echo htmlspecialchars($project['fundgoal']) ?>"/>
                <label for="end-date">End date</label>
                <input id="end-date" name="end-date" placeholder="MM/DD/YYYY" type="date" value="<?php echo htmlspecialchars($project['end_date']) ?>" />
                <h3>Fund Beneficiary</h3>
                <p>Name of the person receiving the funds.</p>
                <div class="half-width first">
                    <label for="first-name">First name</label>
                    <input id="first-name" name="first-name" placeholder="Enter first name" type="text" value="<?php echo htmlspecialchars($project['fname']) ?>" />
                </div>
                <div class="half-width last">
                    <label for="last-name">Last name</label>
                    <input id="last-name" name="last-name" placeholder="Enter last name" type="text" value="<?php echo htmlspecialchars($project['lname']) ?>" />
                </div>
                <div>
                <label for="email">Email</label>
                <input id="email" name="email" placeholder="Enter valid email address" type="email" value="<?php echo htmlspecialchars($project['email']) ?>" />
                </div>
                <label for="phone-number">Phone Number</label>
                <input id="phone-number" name="phone-number" placeholder="Enter valid phone number" type="text" value="0<?php echo htmlspecialchars($project['phoneno']) ?>" />
                <button class="continue-btn" type="submit">Continue</button>
            </form>
        </div>
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
