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

    $countUser = "SELECT COUNT(id) AS userfound FROM users";
    $stmt = $conn->prepare($countUser);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $userfound = $countResult->fetch_assoc();
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
        document.addEventListener("DOMContentLoaded", function() {
            const userProfile = document.querySelector('.logged-in-user-profile');
            const dropdownMenu = document.querySelector('.dropdown-menu');

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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-text"> 
            <div class="crowdfunding-label">
                <span class="line"></span>
                <span class="label-text">Crowdfunding</span>
            </div class="herotext">
            <h1>Empowering Ideas<br>Fueling Innovation Together</h1>
            <p style="max-width: 600px;">This is a space for visionaries and supporters alike. Creators bring their concepts, passion, and vision, while supporters bring the resources to make them real. As a backer, your support empowers creators to turn ambitious ideas into impactful solutions. Together, we're building a community dedicated to innovation and progress. Join us in making ideas come to life.</p>
            <div class="hero-buttons">
                <button class="learn-more"><a href="aboutA.php" style="text-decoration:none;">Learn More</a></button>
                <button class="donate"><a href="projectsA.php" style="text-decoration:none;">Donate</a></button>
            </div>
        </div>
        <div class="hero-image">
            <!-- Main hero image -->
            <img src="../images/Mask Group.png" alt="Donation Image" class="hero-img">
        </div>
    </section>

    <!-- Featured Categories Section -->
    <section class="categories">
        <h2 class="categories-title">Categories</h2>
        <h3 class="categories-subtitle">Explore Our Crowdfunding Featured Categories</h3>
        <p class="categories-description">
            Discover a variety of innovative projects across categories that match your passions and interests. 
            From tech to art, find campaigns that inspire you to make a difference by supporting creators worldwide.
        </p>
        <div class="category-grid">
            <div class="category-card">
                <img src="../images/technology.png" alt="Technology Icon">
                <h4>Technology</h4>
            </div>
            <div class="category-card">
                <img src="../images/health.png" alt="Health Icon">
                <h4>Health</h4>
            </div>
            <div class="category-card">
                <img src="../images/education.png" alt="Education Icon">
                <h4>Education</h4>
            </div>
            <div class="category-card">
                <img src="../images/business.png" alt="Business Icon">
                <h4>Business</h4>
            </div>
            <div class="category-card">
                <img src="../images/environment.png" alt="Environment Icon">
                <h4>Environment</h4>
            </div>
            <div class="category-card">
                <img src="../images/animals.png" alt="Animals Icon">
                <h4>Animals</h4>
            </div>
        </div>
    </section>

    <!-- Recent Projects Section -->
    <section class="recent-projects">
        <h2 class="recent-title">Recent Projects</h2>
        <p class="recent-description">
            Explore our latest projects from innovative creators around the world. Each project represents a unique vision, from groundbreaking tech to creative arts, waiting for supporters like you to help turn ideas into reality.
        </p>
        <div class="project-grid">
            <div class="project-card" data-project-id="2">
            <a href="../html/projectdet.html">
                <img src="../images/project1.png" alt="Project Image" class="project-image">
                <div class="project-content">
                    <span class="project-category">Health</span>
                    <h3>Help Us Bring Life-Saving Treatments to Those in Need</h3>
                    <div class="progress-bar">
                        <div class="progress" style="width: 50%;"></div>
                    </div>
                    <div class="project-details">
                        <div class="project-fund"><img src="../images/moneylogo.svg">10,000</div>
                        <div class="project-time"><img src="../images/calendar.svg">45 Days Left</div>
                    </div>
                </div>
            </a>
            <!-- Author Anchor -->
            <a href="campaigns.html?author=AG" class="project-author">
                <div class="author-icon">AG</div>
                <div class="author-info">
                    <span class="author-name">Anita Gamboa</span>
                    <span><p>5 Campaigns</p> | Cebu City</span>
                </div>
            </a>
        </div>
        <div class="project-card" data-project-id="2">
            <a href="../html/projectdet.html">
                <img src="../images/project1.png" alt="Project Image" class="project-image">
                <div class="project-content">
                    <span class="project-category">Health</span>
                    <h3>Help Us Bring Life-Saving Treatments to Those in Need</h3>
                    <div class="progress-bar">
                        <div class="progress" style="width: 50%;"></div>
                    </div>
                    <div class="project-details">
                        <div class="project-fund"><img src="../images/moneylogo.svg">10,000</div>
                        <div class="project-time"><img src="../images/calendar.svg">45 Days Left</div>
                    </div>
                </div>
            </a>
            <!-- Author Anchor -->
            <a href="campaigns.html?author=AG" class="project-author">
                <div class="author-icon">AG</div>
                <div class="author-info">
                    <span class="author-name">Anita Gamboa</span>
                    <span><p>5 Campaigns</p> | Cebu City</span>
                </div>
            </a>
        </div>
        <div class="project-card" data-project-id="2">
            <a href="../html/projectdet.html">
                <img src="../images/project1.png" alt="Project Image" class="project-image">
                <div class="project-content">
                    <span class="project-category">Health</span>
                    <h3>Help Us Bring Life-Saving Treatments to Those in Need</h3>
                    <div class="progress-bar">
                        <div class="progress" style="width: 50%;"></div>
                    </div>
                    <div class="project-details">
                        <div class="project-fund"><img src="../images/moneylogo.svg">10,000</div>
                        <div class="project-time"><img src="../images/calendar.svg">45 Days Left</div>
                    </div>
                </div>
            </a>
            <!-- Author Anchor -->
            <a href="campaigns.html?author=AG" class="project-author">
                <div class="author-icon">AG</div>
                <div class="author-info">
                    <span class="author-name">Anita Gamboa</span>
                    <span><p>5 Campaigns</p> | Cebu City</span>
                </div>
            </a>
        </div>
        <div class="project-card" data-project-id="2">
            <a href="../html/projectdet.html">
                <img src="../images/project1.png" alt="Project Image" class="project-image">
                <div class="project-content">
                    <span class="project-category">Health</span>
                    <h3>Help Us Bring Life-Saving Treatments to Those in Need</h3>
                    <div class="progress-bar">
                        <div class="progress" style="width: 50%;"></div>
                    </div>
                    <div class="project-details">
                        <div class="project-fund"><img src="../images/moneylogo.svg">10,000</div>
                        <div class="project-time"><img src="../images/calendar.svg">45 Days Left</div>
                    </div>
                </div>
            </a>
            <!-- Author Anchor -->
            <a href="campaigns.html?author=AG" class="project-author">
                <div class="author-icon">AG</div>
                <div class="author-info">
                    <span class="author-name">Anita Gamboa</span>
                    <span><p>5 Campaigns</p> | Cebu City</span>
                </div>
            </a>
        </div>
            
            <!-- Repeat similar structure for other project cards -->
        </div>
    </section>
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

    <section class="team-members" id="team-members">
        <h3 class="team-title">TEAM MEMBER</h3>
        <h2 class="meet-title">Meet Our Team Member</h2>
        <p class="team-description">
            Meet the dedicated team that powers our platform. With diverse expertise and a shared commitment to impact, each team member plays a crucial role in helping us achieve our goals.
        </p>

        <div class="carousel">
            <div class="carousel-track-container">
                <ul class="carousel-track">
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Christine Anne Alesna</p></div></li>
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Clifford Alferez</p></div></li>
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Hersheys Gaboy</p></div></li>
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Jamaica Lapad</p></div></li>
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Daisy Lyn Laygan</p></div></li>
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Jomari Marson</p></div></li>
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Angela A. Postrero</p></div></li>
                    <li class="carousel-slide"><div class="team-member-card"><img src="../images/member.png" alt="Team Member"><p>Jerson Sullano</p></div></li>
                </ul>
            </div>
        </div>
    </section>


    <script>
document.addEventListener("DOMContentLoaded", function () {
    const track = document.querySelector('.carousel-track');
    const slides = Array.from(track.children);
    const slideWidth = slides[0].getBoundingClientRect().width;

    // Initial position
    track.style.transform = `translateX(0px)`;

    // Infinite loop function
    const moveToNextSlide = () => {
        // Slide left by one card
        track.style.transition = 'transform 0.3s linear';
        track.style.transform = `translateX(${-slideWidth}px)`;

        // Wait for the transition to complete
        track.addEventListener('transitionend', () => {
            // Move the first slide to the end
            track.appendChild(slides[0]);

            // Remove the transition for seamless loop
            track.style.transition = 'none';

            // Reset transform to show the updated first slide
            track.style.transform = `translateX(0px)`;

            // Update the slides array
            slides.push(slides.shift());
        }, { once: true });
    };

    // Start infinite loop
    setInterval(moveToNextSlide, 3000); // 3-second interval
});
    </script>
	
	<!-- Statistics Section -->
<section class="statistics">
    <div class="stat-item">
        <h2 class="stat-value" style="color:white;"><?php echo htmlspecialchars($userfound['userfound']) ?></h2>
        <p class="stat-label"style="color:white;">Total Users</p>
    </div>
    <div class="stat-item">
        <h2 class="stat-value"style="color:white;">1k</h2>
        <p class="stat-label"style="color:white;">Donations</p>
    </div>
    <div class="stat-item">
        <h2 class="stat-value"style="color:white;"><?php echo htmlspecialchars($projfound['projfound']) ?></h2>
        <p class="stat-label"style="color:white;">Projects</p>
    </div>
</section

<!-- Banner Section -->
<section class="banner-section">
    <div class="overlay"></div>
    <div class="banner-content">
        <h1>"Help Bring Dreams to Life"</h1>
        <a href="projectsA.html" class="donate-button" style="font-weight:bold;">DONATE</a>
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
