<?php
    session_start();
    include("connection.php");

    if(!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    $idno = $_SESSION['id'];

    $query = "SELECT email, firstname, lastname, profile_pic, CONCAT(firstname, ' ', lastname) AS fullname, CONCAT(SUBSTRING(firstname, 1, 1), SUBSTRING(lastname, 1, 1)) AS default_pic, CONCAT(SUBSTRING(firstname, 1, 1), SUBSTRING(lastname, 1, 1)) AS author_icon FROM users WHERE id = ?";
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
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $author_icon = $user['author_icon'];
        $authorProfilepic = $user['profile_pic'];

        $country = 'Philippines';
        $city = 'Cebu';
        $author = $user['fullname'];
        $title = $_SESSION['project_title'];
        $category = $_SESSION['project_category'];
        $barangay = $_SESSION['barangay'];
        $fund_goal = $_SESSION['fund_goal'];
        $end_date = $_SESSION['end_date'];
        $firstname = $_SESSION['firstname'];
        $lastname = $_SESSION['lastname'];
        $email = $_SESSION['email'];
        $phonenumber = $_SESSION['phone-number'];
        $img = $_SESSION['project_img'];
        $video_url = $_SESSION['video_url'];
        $overview = $_SESSION['project_overview'];
        $story = $_SESSION['project_story'];

        $paymentMethod = $_POST['payment'];

        $conn->begin_transaction();

        try {
            // Insert into `projects` table
            $stmt = $conn->prepare("INSERT INTO projects (user_id, country, city, author, proj_title, category, barangay, fundgoal, created_at, end_date, fname, lname, email, phoneno, project_photo, video_url, project_overview, project_story, author_icon, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "issssssssssssssssss",
                $idno,
                $country,
                $city,
                $author,
                $title,
                $category,
                $barangay,
                $fund_goal,
                $end_date,
                $firstname,
                $lastname,
                $email,
                $phonenumber,
                $img,
                $video_url,
                $overview,
                $story,
                $author_icon,
                $authorProfilepic
            );
            $stmt->execute();
    
            $projectId = $conn->insert_id;
    
            if ($paymentMethod == "PayPal" || $paymentMethod == "GCash" || $paymentMethod == "PayMaya") {
                $stmt = $conn->prepare("INSERT INTO payment_method (payment_method) VALUES (?)");
                $stmt->bind_param("s", $paymentMethod);
            } elseif ($paymentMethod == "Credit/Debit") {
                $cardNumber = $_POST['card_number'];
                $expiryDate = $_POST['expiry_date'];
                $cvv = $_POST['cvv'];
                $cardholderName = $_POST['cardholder_name'];
                $zipCode = $_POST['zipcode'];
    
                if (empty($cardNumber) || empty($expiryDate) || empty($cvv) || empty($cardholderName) || empty($zipCode)) {
                    throw new Exception("All credit/debit card fields are required.");
                }
    
                $stmt = $conn->prepare("INSERT INTO payment_method (proj_id, payment_method, card_number, expiration_date, cvv, cardholder_name, zipcode) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "issssss",
                    $projectId,
                    $paymentMethod,
                    $cardNumber,
                    $expiryDate,
                    $cvv,
                    $cardholderName,
                    $zipCode
                );
            } else {
                throw new Exception("Invalid payment method.");
            }
    
            $stmt->execute();
    
            // Commit the transaction
            $conn->commit();
    
            echo "<script>alert('Payment information saved successfully.');</script>";

            header('Location: done-create.php');
    
            // Clear session variables
            session_unset();
            session_destroy();

            session_start();
            $_SESSION['id'] = $idno;
            
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }

        $conn->close();
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
		.line-1 {
		 height: 20px;
		 }
		.line-2 {
			height: 20px; /* Height of the vertical line */
		}
		.cre-det{
			border:none;
			display: none;
			padding: 0 20px 20px 20px;
			margin-top: 0;
			border-radius: 5px;
			background-color: transparent;
		}
		.cre {
			padding: 0;
			border-radius: 5px;
			margin-top: 0;
		}
	</style>
</head>
<body>
    <!-- Navbar for Logged-in Users -->
    <nav class="logged-in-navbar">
        <div class="logged-in-navbar-logo">
            <a href="lanAfter.html" style="text-decoration:none;">
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
                <span class="logged-in-user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="../images/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="logged-in-profile-img">
                <?php else: ?>
                    <div class="author-icon"><?php echo htmlspecialchars($user['default_pic']) ?></div> <!-- Pa fix ko ani sa front-end di ma show ang default -->
                <?php endif; ?>
                <div class="dropdown-menu">
                    <a href="my-projects.html">My Projects</a>
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
                            <span class="step-number step-number-1"><a href="../html/create1.html" style="text-decoration:none;"><img src="../images/check.svg" alt="Check"></a></span>
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
                            <span class="step-number step-number-1"><a href="../html/create2.html" style="text-decoration:none;"><img src="../images/check.svg" alt="Check"></a></span>
                            <div class="line line-1"></div> <!-- Line under step 2 -->
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
                            <span class="step-number step-number-1"><a href="../html/create3.html" style="text-decoration:none;"><img src="../images/check.svg" alt="Check"></a></span>
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
                            <span class="step-number step-number-1"><img src="../images/pen.png" alt="Pen"></span>
                        </div>
                        <div class="step-text">
                            <span class="text text-4">Payment Methods</span>
                            <span class="description description-1">Add the project overview and the project story of why you’re fundraising.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-content4">
            <form action="create4.php" method="POST">
                <h2>Fundraiser Payment Methods</h2>
                <div class="payment-options">
                    <!-- Paypal Method -->
                    <label for="paypal">
                        <input id="paypal" name="payment" onclick="showPaymentMethod('paypal-details')" value="PayPal" type="radio"/>PayPal</label>
                    <div class="details" id="paypal-details">
                        <div class="payment-method-content">
                            <img alt="PayPal logo" height="50" src="../images/paypal.png" width="100"/>
                            <ul>
                                <li>
                                PayPal is widely available and trusted across Cebu City, Philippines, making it a popular choice, especially among digitally savvy users.
                                </li>
                                <li>
                                Set up a new PayPal account or link an existing one to your fundraising page in just seconds.
                                </li>
                            </ul>
                            <button>Link with PayPal</button>
                        </div>
                    </div>

                    <!-- GCash Method -->
                    <label for="gcash">
                        <input id="gcash" name="payment" onclick="showPaymentMethod('gcash-details')" value="GCash" type="radio"/>GCash
                    </label>
                    <div class="details" id="gcash-details">
                        <div class="payment-method-content">
                            <img alt="GCash logo" height="50" src="../images/gcash.png" width="100"/>
                            <ul>
                                <li>
                                GCash is available in all parts of Cebu City, Philippines, and it's a commonly preferred payment method, especially amongst the more Internet-savvy.
                                </li>
                                <li>
                                Create an account at GCash or connect an existing account to your fundraising page in seconds.
                                </li>
                            </ul>
                            <button>Link with GCash</button>
                        </div>
                    </div>

                    <!-- PayMaya Method -->
                    <label for="paymaya">
                        <input id="paymaya" name="payment" onclick="showPaymentMethod('paymaya-details')" value="PayMaya" type="radio"/>PayMaya
                    </label>
                    <div class="details" id="paymaya-details">
                        <div class="payment-method-content" style="padding-top: 15px;">
                            <h2 style="text-align:center; color:#01B463; font-size: 36px; padding-bottom: 10px;">PayMaya</h2>
                            <ul>
                                <li>PayMaya is accessible throughout Cebu City, Philippines, and is a favored payment option, particularly among tech-savvy users.</li>
                                <li>Set up a new PayMaya account or connect an existing one to your fundraising page in just seconds.</li>
                            </ul>
                            <button>
                                Link with PayMaya
                            </button>
                        </div>
                    </div>
                    
                    <!-- Credit or Debit method -->
                    <label for="credit-debit">
                        <input id="credit-debit" name="payment" onclick="showPaymentMethod('credit-debit-details')" value="Credit/Debit" type="radio"/>Credit or Debit
                    </label>
                    <div class="details cre-det" id="credit-debit-details">
                        <div class="payment-method-content cre" style="background-color: transparent; border:none;">
                            <input class="cardnum" placeholder="Card number" name="card_number" type="text"/>
                            <div class="form-group">
                                <input placeholder="MM/YY" name="expiry_date" type="text"/>
                                <input placeholder="CVV" name="cvv" type="number" pattern="[0-9]{3}"/>
                            </div>
                            <input class="cardname" placeholder="Cardholder's name" name="cardholder_name" type="text"/>
                            <div class="form-group">
                                <input readonly type="text" value="Cebu, Philippines"/>
                                <input placeholder="6000" name="zipcode" type="number" pattern="[0-9]{4}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="submit-button">
                    <button class="submit-btn" type="submit">Submit</button>
                </div>
            </form>
        </div>
        <script>
        function showPaymentMethod(method) {
                    var methods = document.querySelectorAll('.details');
                    methods.forEach(function (el) {
                        el.style.display = 'none';
                    });
                    document.getElementById(method).style.display = 'block';
                }
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
