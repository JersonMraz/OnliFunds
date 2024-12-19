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

    $project_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($project_id > 0) {
        $stmt = $conn->prepare("SELECT *, DATEDIFF(end_date, NOW()) AS remaining_days, COUNT(user_id) AS all_proj FROM projects WHERE user_id = ?");
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

    $fetchProject = "SELECT *, DATEDIFF(end_date, NOW()) AS remaining_days FROM projects WHERE user_id = ?";
    $stmt = $conn->prepare($fetchProject);
    $stmt->bind_param("i", $project_id);
	$stmt->execute();
	$result = $stmt->get_result();

	if($result->num_rows > 0) {
		$rows = $result->fetch_all(MYSQLI_ASSOC);
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnliFunds</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Allura&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMtl/tE7HT72fSUkSuZXCe9jKg6abX5xnu2bDbD" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
	<style>
	.project-author {
    display: flex;
    align-items: center; /* Center the items vertically */
    margin-top: 10px; /* Add some spacing above */
        }

        .author-icon {
            background-color: #1B8271; /* Example background color */
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px; /* Space between icon and text */
        }

        .author-info {
            display: flex;
            flex-direction: column; /* Stack name and info vertically */
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
                    <a href="profile-settings.html">Profile Settings</a>
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
            <h1>Backed Projects</h1>
        </div>
    </section>

    <!-- Recent Projects Section -->
    <section class="campaigns">
        <div class="authorprof">
            <?php if (!empty($project['profile_pic'])): ?>
                <img src="../images/<?php echo htmlspecialchars($project['profile_pic']); ?>" alt="Profile Picture" class="author-profile">
            <?php else: ?>
                <div class="author-icon"><?php echo htmlspecialchars($project['author_icon']) ?></div>
            <?php endif; ?>
            <div class="author-info">
            <span class="author-name"><?php echo htmlspecialchars($project['author']) ?></span>
            </div>
        </div>
        <div class="campaign-cont">
            <div class="project-cards">
                <?php foreach($rows as $row): ?>
                    <div class="project-card">
                        <a href="../html/projectdet.php">
                            <img src="../images/<?php echo htmlspecialchars($row['project_photo']) ?>" alt="Project Image" class="project-image">
                            <div class="project-content">
                                <span class="project-category"><?php echo htmlspecialchars($row['category']) ?></span>
                                <h3><?php echo htmlspecialchars($row['proj_title']) ?></h3>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 0%;"></div>
                                </div>
                                <div class="project-details">
                                    <div class="project-fund"><img src="../images/moneylogo.svg"><?php echo htmlspecialchars($row['fundgoal']) ?></div>
                                    <div class="project-time"><img src="../images/calendar.svg"><?php echo htmlspecialchars($row['remaining_days']) ?> Days Left</div>
                                </div>
                            </div>
                        </a>
                        <!-- Author Anchor -->
                        <a href="campaigns.html?author=AG" class="project-author">
                            <?php if (!empty($row['profile_pic'])): ?>
                                <img src="../images/<?php echo htmlspecialchars($row['profile_pic']); ?>" alt="Profile Picture" class="author-profile">
                            <?php else: ?>
                                <div class="author-icon"><?php echo htmlspecialchars($row['author_icon']) ?></div>
                            <?php endif; ?>
                            <div class="author-info">
                                <span class="author-name"><?php echo htmlspecialchars($row['author']) ?></span>
                                <span><p><?php echo htmlspecialchars($project['all_proj']) ?> Campaigns</p> | Cebu City</span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Get all the project cards and authors
                        const projectCards = document.querySelectorAll('.project-card');
                        const authors = document.querySelectorAll('.project-author');

                        // Handle project card click
                        projectCards.forEach(card => {
                            card.addEventListener('click', function(event) {
                                // Prevent link from opening immediately
                                event.preventDefault();
                                
                                // Get the project ID from the clicked card
                                const projectId = card.getAttribute('data-project-id');
                                
                                // Redirect to the project details page (or you could add more dynamic URL)
                                window.location.href = `../html/projectdet.html?project=${projectId}`;
                            });
                        });

                        // Handle author click with redirect using JavaScript
                        authors.forEach(author => {
                            author.addEventListener('click', function(event) {
                                // Prevent the project card click from triggering
                                event.stopPropagation();
                                
                                // Get the author ID from the clicked author
                                const authorId = author.getAttribute('data-author-id');
                                
                                // Redirect to the author's campaign page
                                window.location.href = `campaigns.html?author=${authorId}`;
                            });
                        });
                    });
                </script>
            </div>
    </section>

                 <!-- Footer Section -->
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
								<li><a href="#"><img src="facebook.png" alt="Facebook Icon" style="	width: 28px;height:auto;"> Facebook</a></li>
								<li><a href="#"><img src="twitter.png" alt="Twitter Icon" style="	width: 28px;height:auto;"> Twitter</a></li>
								<li><a href="#"><img src="instagram.png" alt="Instagram Icon" style="	width: 28px;height:auto;"> Instagram</a></li>
								<li><a href="#"><img src="linkedin.png" alt="LinkedIn Icon" style="	width: 28px;height:auto;"> LinkedIn</a></li>
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