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

    $countProject = "SELECT user_id, COUNT(proj_id) AS projfound FROM projects";
    $stmt = $conn->prepare($countProject);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $projfound = $countResult->fetch_assoc();

    $fetchProject = "SELECT p.user_id, p.proj_id, p.proj_title, p.category, p.city, p.author, p.fundgoal, p.project_photo, p.author_icon, p.profile_pic, DATEDIFF(p.end_date, NOW()) AS remaining_days, 
                    (SELECT COUNT(proj_id) FROM projects WHERE user_id = p.user_id) AS projfound
                    FROM projects p";
    $stmt = $conn->prepare($fetchProject);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    }

    $fetchDonation = "SELECT SUM(donation_amount) AS total_donated FROM donation WHERE proj_id = ?";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnliFunds</title>
    <link rel="stylesheet" href="../css/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap">
	<link href="https://fonts.googleapis.com/css2?family=Allura&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMtl/tE7HT72fSUkSuZXCe9jKg6abX5xnu2bDbD" crossorigin="anonymous">
</head>
<body>
		<!-- Navbar -->
	<nav class="navbar-transparent">
		<div class="navbar-logo">
			<a href="lanAfter.php" style="text-decoration:none;">
				<img src="../images/logo.png" alt="OnliFunds Logo" class="logo-img">
			</a>
			<div class="logo-text">
				<h1><a href="lanAfter.php" style="text-decoration:none;">OnliFunds</a></h1>
				<p>Empower Your Ideas</p>
			</div>
		</div>
		<div class="nav-links">
			<a href="lanAfter.php" class="nav-item">Home</a>
			<a href="aboutA.php" class="nav-item">About</a>
			<a href="projectsA.php" class="nav-item">Projects</a>
			<a href="create1.php" class="logged-in-nav-item logged-in-start-project-button">Start a Project</a>
		<div class="logged-in-user-profil">
            <span class="logged-in-user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
			<?php if (!empty($user['profile_pic'])): ?>
                <img src="../images/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="logged-in-profile-img">
            <?php else: ?>
                <div class="author-icon"><?php echo htmlspecialchars($user['default_pic']) ?></div> <!-- Pa fix ko ani sa front-end di ma show ang default -->
            <?php endif; ?>
			<div class="dropdown-men">
				<a href="my-projects.php" style="color:black;">My Projects</a>
				<a href="backed-projects.html">Backed Projects</a>
				<a href="profile-settings.php">Profile Settings</a>
				<a href="login.php">Log Out</a>
			</div>
		</div>
		</div>
		</div>
	</nav>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const userProfile = document.querySelector('.logged-in-user-profil');
			const dropdownMenu = document.querySelector('.dropdown-men');

			userProfile.addEventListener('click', function() {
				dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
			});

			// Close the dropdown if clicked outside
			document.addEventListener('click', function(e) {
				if (!userProfile.contains(e.target)) {
					dropdownMenu.style.display = 'none';
				}
			});
		});
	</script>

	<section class="projects-hero">
		<h1 class="projects-heading">Let’s find crowdfunding projects <br>around the world</h1>
	</section>

	<div class="search-bar-container">
		<select class="category-dropdown">
			<option>All Category</option>
			<option>Technology</option>
			<option>Health</option>
			<option>Education</option>
			<option>Business</option>
			<option>Environment</option>
			<option>Animals</option>
		</select>
		<input type="text" placeholder="Find Projects" class="search-input">
		<button class="search-button"><i class="fa fa-search"></i></button>
	</div>
	<div class="projects-container">
		<!-- Filter Sidebar -->
		<div class="con">
			<div class="filter-title"><h2>Filter</h2></div>
			<aside class="filter-sidebar">
				<div class="filter-category">
					<h4>Category</h4>
					<ul>
						<li><input type="checkbox" class="category" checked> All Projects</li>
						<li><input type="checkbox" class="category" value="Technology"> Technology</li>
						<li><input type="checkbox" class="category" value="Health"> Health</li>
						<li><input type="checkbox" class="category" value="Education"> Education</li>
						<li><input type="checkbox" class="category" value="Business"> Business</li>
						<li><input type="checkbox" class="category" value="Environment"> Environment</li>
						<li><input type="checkbox" class="category" value="Animals"> Animals</li>
					</ul>
					<button class="search-projects-button" onclick="applyFilters()">SEARCH PROJECTS</button>
				</div>
			</aside>
		</div>

		 <!-- Projects List -->
		<section class="projects-list">
			<!-- Popular Search Links -->
			<div class="popular-search" style="font-weight:bold;">
				<span style="padding-right: 15px;">Popular Search:</span>
				<a href="#">Technology Projects</a>
				<a href="#">Cancer Charity Programs</a>
				<a href="#">Charity for Strays</a>
			</div>

        <!-- Number of Projects and Sorting -->
        <div class="projects-header">
            <h2> <?php echo $projfound['projfound'] ?> Projects Found</h2>
            <select class="sort-dropdown">
                <option>Newest</option>
            </select>
        </div>

        <div class="project-cards">
            <!-- Project Card 1 -->
            <?php foreach($rows as $row): ?>
				<?php
					// Fetch the total donations for the current project
					$stmt = $conn->prepare($fetchDonation);
					$stmt->bind_param("i", $row['proj_id']);
					$stmt->execute();
					$donationResult = $stmt->get_result();
					$donation = $donationResult->fetch_assoc();

					$totalDonated = !empty($donation['total_donated']) ? floatval($donation['total_donated']) : 0;
					$fundGoal = !empty($row['fundgoal']) ? floatval(str_replace(',', '', $row['fundgoal'])) : 0;

					if ($fundGoal > 0) {
						$progressPercentage = ($totalDonated / $fundGoal) * 100;
						$progressPercentage = min(100, max(0, $progressPercentage));  // Ensure it's between 0% and 100%
					} else {
						$progressPercentage = 0;
					}
				?>

				<div class="project-card">
					<a href="../html/projectdet.php?proj_id=<?php echo htmlspecialchars($row['proj_id']); ?>, author=<?php echo htmlspecialchars($row['author']); ?>">
						<img src="../images/<?php echo htmlspecialchars($row['project_photo']) ?>" alt="Project Image" class="project-image">
						<div class="project-content">
							<span class="project-category"><?php echo htmlspecialchars($row['category']) ?></span>
							<h3><?php echo htmlspecialchars($row['proj_title']) ?></h3>
							<div class="progress-bar">
								<div class="progress" style="width: <?php echo round($progressPercentage, 2) ?>%;"></div>
							</div>
							<div class="project-details">
								<div class="project-fund"><img src="../images/moneylogo.svg"><?php echo htmlspecialchars($row['fundgoal']) ?></div>
								<div class="project-time"><img src="../images/calendar.svg"><?php echo htmlspecialchars($row['remaining_days']) ?> Days Left</div>
							</div>
						</div>
					</a>
					<!-- Author Anchor -->
					<a href="campaigns.php?user_id=<?php echo htmlspecialchars($row['user_id']); ?>" class="project-author">
						<?php if (!empty($row['profile_pic'])): ?>
							<img class="author-profile" src="../images/<?php echo htmlspecialchars($row['profile_pic']); ?>" alt="Author Profile">
						<?php else: ?>
							<div class="author-icon"><?php echo htmlspecialchars($row['author_icon']) ?></div>
						<?php endif; ?>
						<div class="author-info">
							<span class="author-name"><?php echo htmlspecialchars($row['author']) ?></span>
							<span><p><?php echo htmlspecialchars($row['projfound']); ?> Campaigns</p> | <?php echo htmlspecialchars($row['city']) ?></span>
						</div>
					</a>
				</div>
            <?php endforeach; ?>
        </div>
		<!--<button class="load-more">LOAD MORE</button>-->
		</section>
	</div>
	<!-- CTA Banner Section -->
	<section class="banner-section">
		<div class="overlay"></div>
		<div class="banner-content">
			<h1>"Help Bring Dreams to Life"</h1>
			<a href="projectsA.php" class="donate-button" style="font-weight:bold;">DONATE</a>
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
					<li><a href="#"><img src="../images/facebook.png" alt="Facebook Icon" style="	width: 28px;height:auto;"> Facebook</a></li>
					<li><a href="#"><img src="../images/twitter.png" alt="Twitter Icon" style="	width: 28px;height:auto;"> Twitter</a></li>
					<li><a href="#"><img src="../images/instagram.png" alt="Instagram Icon" style="	width: 28px;height:auto;"> Instagram</a></li>
					<li><a href="#"><img src="../images/linkedin.png" alt="LinkedIn Icon" style="	width: 28px;height:auto;"> LinkedIn</a></li>
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
