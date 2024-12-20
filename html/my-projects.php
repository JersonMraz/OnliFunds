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

    $fetchProject = "SELECT *, DATEDIFF(end_date, NOW()) AS remaining_days FROM projects WHERE user_id = ?";
    $stmt = $conn->prepare($fetchProject);
    $stmt->bind_param("i", $idno);
	$stmt->execute();
	$result = $stmt->get_result();

	$projects = [];
    if ($result->num_rows > 0) {
        $projects = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($projects as $key => $project) {
            $projId = $project['proj_id'];

            $fetchDonation = "SELECT SUM(donation_amount) AS total_donations FROM donation WHERE proj_id = ?";
            $donationStmt = $conn->prepare($fetchDonation);
            $donationStmt->bind_param("i", $projId);
            $donationStmt->execute();
            $donationResult = $donationStmt->get_result();
            $donationData = $donationResult->fetch_assoc();

            $donationAmount = $donationData['total_donations'] ?? 0; // Default to 0 if no donations found

            // Calculate progress percentage
            $fundGoal = str_replace(',', '', $project['fundgoal']);
            $fundGoal = floatval($fundGoal);

            if ($fundGoal > 0) {
                $progressPercentage = ($donationAmount / $fundGoal) * 100;
            } else {
                $progressPercentage = 0;
            }

            $progressPercentage = min(100, max(0, $progressPercentage)); // Ensure percentage is between 0 and 100

            // Update project with calculated values
            $projects[$key]['donation_amount'] = $donationAmount;
            $projects[$key]['progress_percentage'] = $progressPercentage;
        }
    }

    $status = isset($_GET['status']) ? $_GET['status'] : null;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMtl/tE7HT72fSUkSuZXCe9jKg6abX5xnu2bDbD" crossorigin="anonymous">
    <style>
        .overlays {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(65, 149, 134, 0.7); /* Semi-transparent background */
            z-index: 9999;
            display: none; /* Initially hidden */
        }
                * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body{
        background-color: #FBFBFB;
        }
        .project-detail {
            margin: 50px 100px;
        }
        .author-info span p{
            margin: 0;
        }

        .project-header {
            display: flex;
            gap: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .carousel-inner img {
            width: 551px;
            height: 419px;
            display: block;
        }

        .carousel {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
        }

        .carousel-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.8);
            border: none;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
        }

        .carousel-control.prev {
            left: 10px;
        }

        .carousel-control.next {
            right: 10px;
        }

        .thumbnail-images {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            justify-content: center;
        }

        .thumbnail-images img{
            width: 129px;
            height:109px;
        }

        .thumbnail {
            width: 60px;
            height: 60px;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

            .thumbnail:hover {
                transform: scale(1.1);
            }

        .share-icons {
            font-size: 14px;
            margin-left: auto;
            color: #888;
        }

        .category {
            color: #1B8271;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .project-info h2 {
            margin: 10px 0;
            font-size: 30px;
            font-weight: bold;
            color: black;
        }

        .description {
            color: black;
            margin-bottom:45px;
            margin-right: 25px;
        }

        .progress-bar {
            background: #f1f1f1;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
			margin-top: 10px;
            position: relative;
            height: 10px;
		
        }

        .progress {
            height: 50px;
            background-color: #D55E5E;
            text-align: center;
            color: #fff;
            font-size: 20px;
            line-height: 30px;
        }

        .funding-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 45px;
        }
        .stat-ite {
            display: flex;
            align-items: flex-start;
            margin: 0 20px;
        }

        .stat-ite i {
            font-size: 24px; /* Adjust size as needed */
            margin-right: 10px; /* Spacing between icon and text */
            color: #333; /* Customize the color */
        }

        .stat-content span {
            font-weight: bold; /* Bold for emphasis */
            font-size: 15px; /* Adjust size as needed */
        }

        .stat-content p {
            margin: 0;
            font-size: 13px; /* Adjust size as needed */
            color: #666; /* Optional: Adjust text color */
        }


        .fund-button {
            background-color: rgba(27, 130, 113, 1);
            color: white;
            border: none;
            width: 218.56px;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }
		.m-p-d-info-part-3{
        display: flex;
        justify-content: space-between;
        margin: 1% 4% 3% 2%;
        }
        .my-4{
            margin-bottom: 0;
        }

        .dets-cont {
            display: flex;
        }

        .dets-cont h1{
            font-size: 20px;
            font-weight: 600;
            margin-top: 45px;
        }

        .dets-cont img {
            margin-right: 15px;
            width: 23px;
            height:auto;
        }
        /* PROJECT DETAILS PROGRESS BAR */
        .custom-progress-container {
            position: relative;
            display: flex;
            align-items: center;
                margin-right: 20px;

        }

        .custom-progress {
            background-color: #e9ecef; /* Light grey background */
            border-radius: 5px;}

        .custom-progress-bar {
            background-color: #ff6f61; /* Custom progress color */
            height: 10px;
            border-radius: 5px;
            position: relative;
        }

        .custom-progress-label {
            position: absolute;
            top: -35px; /* Position above the progress bar */
            right: 0; /* Align with the end of progress */
            transform: translateX(50%); /* Center the label */
            background-color: #ff6f61;
            color: #fff;
            font-size: 12px;
            font-weight: 500pxh;
            padding: 5px 10px;
            border-radius: 20px; /* Rounded label */
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .custom-progress-label::after {
            content: "";
            position: absolute;
            bottom: -10px; /* Creates the small pointer */
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: #ff6f61 transparent transparent transparent;
            margin-top: 20px;
        }
        /* Banner Section Styling */
        .banner1 {
            position: relative;
            width: 100%;
            height: 165px;
            background-image: url("../images/banner2.png"); /* Ensure this path is correct */
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #419586;
            opacity: 0.6; /* 60% overlay */
            z-index: 1;
        }

        .banner-content {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
        }

        .banner-content h1 {
            font-size: 36px;
            font-weight: bold;
            margin: 0 0 20px 0;
        }

        .cont{
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            margin: 0 100px;
            margin-bottom: 120px;
            min-height: 100vh; 
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
                <h1><a href="lanAfter.html" style="text-decoration:none; color:black;">OnliFunds</a></h1>
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
            <h1>Your Campaigns</h1>
        </div>
    </section>
     <!-- CONTENT -->
    <?php foreach($projects as $project): ?>
        <?php 
            $start_date = $project['created_at'];
            $due_date = $project['end_date'];

            $start_date_obj = new DateTime($start_date);
            $end_date_obj = new DateTime($due_date);

            $formatted_start_date = $start_date_obj->format('F d, Y');
            $formatted_end_date = $end_date_obj->format('F d, Y');
        ?>
        <section class="content-mp">
            <div class="content-container">
                <div class="content-left">
                    <img src="../images/<?php echo htmlspecialchars($project['project_photo']) ?>" alt="">
                    <div class="c-l-1">
                        <h1><?php echo htmlspecialchars($project['category']) ?></h1>
                        <h2>Started on <?php echo $formatted_start_date ?></h2>
                    </div>
                    <h3><?php echo htmlspecialchars($project['proj_title']) ?></h3>
                    <div class="custom-progress-container my-4">
                        <!-- Progress Bar -->
                        <div class="custom-progress w-100 position-relative" style="height: 10px; border-radius: 5px;">
                            <div class="custom-progress-bar" role="progressbar" style="width: <?php echo $project['progress_percentage']; ?>%;" aria-valuenow="<?php echo $project['progress_percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                                <!-- Percentage Label -->
                                <span class="custom-progress-label"><?php echo round($project['progress_percentage'], 2); ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div class="c-l-2">
                            <div class="dets-cont">
                                <img src="../OnliFunds/images/fundgoal.svg" alt="">
                                <div class="dets-conts-2">
                                    <h1>Php <?php echo htmlspecialchars($project['fundgoal']) ?></h1>
                                    <p>Fund Goal</p>
                                </div>
                            </div>
                            <div class="dets-cont">
                                <img src="../OnliFunds/images/moneylogo.svg" alt="">
                                <div class="dets-conts-2">
                                    <h1>Php <?php echo htmlspecialchars($project['donation_amount']); ?></h1>
                                    <p>Total Raised</p>
                                </div>
                            </div>
                    </div>
                    <div class="c-l-3">
                        <div class="donors">
                            <h4>Donors:</h4>
                            <h4>Comments:</h4>
                            <h4>Start Date:</h4>
                            <h4>End Date:</h4>
                        </div>
                        <div class="donors-data">
                            <h5>0</h5>
                            <h5>2</h5>
                            <h5><?php echo $formatted_start_date ?></h5>
                            <h5><?php echo $formatted_end_date ?></h5>
                        </div>

                    </div>
                    <div class="view-campaign-button">
                        <a href="user-proj-det.html">View campaign</a>
                    </div>
                </div>
                <div class="content-right">
                    <form action="my-projects.php" method="post">
                        <div class="cont-right-info">
                            <img src="../OnliFunds/images/`share.svg" alt="">
                            <a href="#">Share with friends</a>
                        </div>
                        <div class="cont-right-info">
                            <img src="../OnliFunds/images/add.svg" alt="">
                            <a href="user-proj-det.html#menu-about-upd">Add a campaign update</a>
                        </div>
                        <div class="cont-right-info">
                            <img src="../OnliFunds/images/edit.svg" alt="">
                            <a href="../html/edit1.php?proj_id=<?php echo htmlspecialchars($project['proj_id']); ?>">Edit campaign</a>
                        </div>
                        <div class="cont-right-info">
                            <img src="../OnliFunds/images/withdraw.svg" alt="">
                            <a href="withdraw.html">Withdraw</a>
                        </div>
                        <div class="cont-right-info">
                            <img src="../OnliFunds/images/Transfer.svg" alt="">
                            <a href="withdrawalHist.html">Withdrawal History</a>
                        </div>
                        <div class="cont-right-info">
                            <img src="../OnliFunds/images/delete.svg" alt="">
                            <a href="javascript:void(0);" class="delete-btn" data-id="<?php echo htmlspecialchars($project['proj_id']); ?>">
                                Delete campaign
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const campaignId = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'delete-campaign.php?id=' + campaignId;
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'success') {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'One campaign has been deleted.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else if (status === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: 'There was an error deleting the campaign. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else if (status === 'invalid') {
                Swal.fire({
                    title: 'Invalid Request!',
                    text: 'The campaign ID provided is invalid.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
   

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
	
	    <!-- BOOTSTRAP SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    <!-- JAVASCRIPT -->
    <script>
        function showSection(sectionId, button) {
          // Hide all sections
          const sections = document.querySelectorAll('.content-section');
          sections.forEach(section => {
            section.classList.remove('active');
          });
    
          // Show the selected section
          const activeSection = document.getElementById(sectionId);
          if (activeSection) {
            activeSection.classList.add('active');
          }
    
          // Remove active state from all buttons
          const buttons = document.querySelectorAll('#menu button');
          buttons.forEach(btn => {
            btn.classList.remove('active-btn');
          });
    
          // Add active state to the clicked button
          button.classList.add('active-btn');
        }
      </script>
	  <script>
// Function to return the update details content
function getReport() {
    return `
        <div class="report">
			<div class="modal-header">
				<div class="closes">
				<span class="close">&times;</span></div>
				<div><h2>Report Violation</h2></div>
			</div>
			<div class="modal-body">
				<label for="violation-type">Violation Type</label>
				<select id="violation-type">
					<option value="" disabled selected>Please Select</option>
					<option value="">Inappropriate Content</option>
					<option value="">Misleading Information</option>
					<option value="">Graphic or Violent Content</option>
					<option value="">Fraudulent Activity</option>
					<option value="">Dangerous Proposals</option>
					<option value="">Violation of Terms of Service</option>
					<!-- Add other options as needed -->
				</select>
				<label for="reason">Explain the Reason</label>
				<textarea id="reason" placeholder="Type here..."></textarea>
			</div>
			<div class="modal-foot">
				<button type="button">Submit</button>
			</div>
    </div>
    `;
}

function showOver(event) {
    event.preventDefault();
    console.log("Over link clicked"); // Debugging line

    // Create the overlay div
    const over = document.createElement('div');
    over.classList.add('overlays');

    // Create the content for the overlay
    const report = document.createElement('div');
    report.innerHTML = getReport(); // Call the function to get the content

    // Append the details to the overlay
    over.appendChild(report);
    document.body.appendChild(over);
    over.style.display = 'block';
	
    // Add functionality to the close button
    const closeButton = report.querySelector('.close');
    closeButton.addEventListener('click', function() {
        document.body.removeChild(over); // Remove the overlay when close button is clicked
    });

    // Add event listener to remove the overlay when clicked outside the modal
    over.addEventListener('click', function(event) {
        // If the click is outside the modal content, close the overlay
        if (!report.contains(event.target)) {
            document.body.removeChild(over);
        }
    });
}
// Function to toggle the heart icon
function toggleHeart(event) {
    const heartIcon = event.target;
    
    // Toggle the heart icon class
    if (heartIcon.classList.contains('far')) {
        heartIcon.classList.remove('far');
        heartIcon.classList.add('fas');
    } else {
        heartIcon.classList.remove('fas');
        heartIcon.classList.add('far');
    }
}

// Add event listeners for heart icon and flag button
document.querySelectorAll('.heartflag button i.far').forEach(heart => {
    heart.addEventListener('click', toggleHeart);
});

document.querySelectorAll('.showOver').forEach(link => {
    link.addEventListener('click', showOver);
});
</script>
<script>
// Function to return the update details content
function getUpdateDetails() {
    return `
        <div class="update-details">
			<div class="modal-header">
				<div class="closes">
				<span class="close">&times;</span></div>
			</div>
            <h2>Supplies Have Been Distributed</h2>
			<p class="up">Update posted by <a href="#">Elliot Bac</a> at 02:47 pm</p>
			<img alt="campaign image" src="../images/health23.png">
            <p>Hello everyone! <br><br> We hope you're all doing well! We're excited to share an important update on our crowdfunding campaign. Thanks to your incredible support, we’ve successfully distributed all the supplies to the teams and individuals involved. 
			It’s an exciting milestone as we move closer to achieving our goal. Each item has been carefully allocated, ensuring that the materials are in the hands of those who need them the most.
<br><br>This wouldn’t be possible without you – your contributions are making a real impact, and we can already see the positive changes starting to take place. The next phase is underway, and we're eager to see how these supplies help bring our vision to life.
<br><br>We are deeply grateful for your continued support, and we promise to keep you updated with every step forward. Stay tuned for more news as we work towards reaching our campaign goal and making a difference together!
<br><br>Thank you again for being part of this journey with us – we couldn’t do it without you!</p>
            <button id="backToCampaign">Back to campaign page</button>
        </div>
    `;
}

function showOverlay(event) {
    event.preventDefault();
    console.log("Overlay link clicked"); // Debugging line

    // Create the overlay div
    const overlay = document.createElement('div');
    overlay.classList.add('overlays');

    // Create the content for the overlay
    const updateDetails = document.createElement('div');
    updateDetails.innerHTML = getUpdateDetails(); // Call the function to get the content

    // Append the details to the overlay
    overlay.appendChild(updateDetails);
    document.body.appendChild(overlay);
    overlay.style.display = 'block';

    // Add event listener to remove the overlay when clicked
    overlay.addEventListener('click', function() {
        document.body.removeChild(overlay);
    });

   // Add functionality to the back button to close the modal
    const backButton = report.querySelector('#backToCampaign');
    if (backButton) {
        backButton.addEventListener('click', function() {
            document.body.removeChild(over); // Close the modal when back button is clicked
            // Optionally, you can still navigate to the campaign page
            window.location.href = 'campaign_page_url'; // Replace with actual campaign page URL
        });
    }
}

document.querySelectorAll('.showOverlayLink').forEach(link => {
    link.addEventListener('click', showOverlay);
});
</script>

</body>
</html>
