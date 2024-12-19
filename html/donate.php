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
        // Query the database for the project details
        $stmt = $conn->prepare("SELECT *, DATEDIFF(end_date, NOW()) AS remaining_days FROM projects WHERE proj_id = ?");
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

    $stmt = $conn->prepare("SELECT COUNT(user_id) AS userfound FROM donation WHERE user_id = ?");
    $stmt->bind_param("i", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    $userFound = $result->fetch_assoc();

    $paymentMethod = "None"; // Default value

    if ($result->num_rows > 0) {
        $method = $result->fetch_assoc();
        $paymentMethod = $method['payment_method'] ?? "None";
    }

    echo "<script>var currentPaymentMethod = '$paymentMethod';</script>";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $amountFunded = isset($_POST['amount-funded']) ? intval($_POST['amount-funded']) : 0;
        $payment = isset($_POST['payment']) ? $_POST['payment'] : null;

        if ($amountFunded <= 0) {
            echo "<script>alert('Please enter a valid donation amount.')</script>";
            exit();
        }
    
        if (empty($payment)) {
            echo "<script>alert('Please select a payment method.')</script>";
            exit();
        }

        $query = "SELECT donation_amount FROM donation WHERE user_id = ? AND proj_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $idno, $project_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User already donated; update the existing donation
            $existingDonation = $result->fetch_assoc();
            $newAmount = $existingDonation['donation_amount'] + $amountFunded;

            $updateQuery = "UPDATE donation SET donation_amount = ?, payment_method = ? WHERE user_id = ? AND proj_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("isii", $newAmount, $payment, $idno, $project_id);
            $updateStmt->execute();
        } else {
            // User has not donated yet; insert a new donation
            $insertQuery = "INSERT INTO donation(user_id, proj_id, payment_method, donation_amount) VALUES(?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iisi", $idno, $project_id, $payment, $amountFunded);
            $insertStmt->execute();
        }

        header("location: done-donate.php");
        exit();
    }
    $stmt->close();
    $conn->close();
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
            <h1>Donate</h1>
        </div>
    </section>


    <!-- CONTENT -->
    <section class="create-container-withdraw" >
        <div class="main-content4">
            <form action="donate.php?proj_id=<?php echo htmlspecialchars($project_id) ?>" method="post">
                <div class="choose-donation-amount">
                    <img src="../images/<?php echo htmlspecialchars($project['project_photo']) ?>" alt="">
                    <div class="choose-d-a-info">
                        <h1>Choose your donation amount for</h1>
                        <h2><?php echo htmlspecialchars($project['proj_title']) ?></h2>
                    </div>
                </div>
                    <h2>Enter the amount</h1>
                <div class="enter-amount-opt">
                    <div class="amount-opt-list">
                        <button class="toggleButton" type="button" data-value="50">₱50</button>
                        <button class="toggleButton" type="button" data-value="100">₱100</button>
                        <button class="toggleButton" type="button" data-value="200">₱200</button>
                        <button class="toggleButton" type="button" data-value="500">₱500</button>
                        <button class="toggleButton" type="button" data-value="1000">₱1000</button>
                    </div>
                    <div class="input-amount-opt">
                        <div class="input-a-opt-sign">
                            <h1>&#8369;</h1>
                            <h2>PHP</h2>
                        </div>
                        <input type="number" name="amount-funded" id="amount-funded"> 
                    </div>
                </div>

                <h2> Payment method </h2>
                <div class="payment-opt-cont">
                    <div class="payment-options">
                        <label for="paypal">
                        <input id="paypal" name="payment" onclick="showPaymentMethod('paypal-details')" type="radio"/>
                        PayPal
                        </label>
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
                                <button id="paypal-link-button" onclick="toggleLink('paypal')">
                                    Link with PayPal
                                </button>
                            </div>
                        </div>
                        <label for="gcash">
                        <input id="gcash" name="payment" onclick="showPaymentMethod('gcash-details')" type="radio"/>
                        GCash
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
                                <button id="gcash-link-button" onclick="toggleLink('gcash')">
                                    Unlink with GCash
                                </button>
                            </div>
                        </div>
                        <label for="paymaya">
                        <input id="paymaya" name="payment" onclick="showPaymentMethod('paymaya-details')" type="radio"/>
                        PayMaya
                        </label>
                        <div class="details" id="paymaya-details">
                            <div class="payment-method-content" style="padding-top: 15px;">
                                <h2 style="text-align:center; color:#01B463; font-size: 36px; padding-bottom: 10px;">
                                    PayMaya
                                </h2>
                                <ul>
                                    <li>
                                    PayMaya is accessible throughout Cebu City, Philippines, and is a favored payment option, particularly among tech-savvy users.
                                    </li>
                                    <li>
                                    Set up a new PayMaya account or connect an existing one to your fundraising page in just seconds.
                                    </li>
                                </ul>
                                <button id="paymaya-link-button" onclick="toggleLink('paymaya')">
                                    Link with PayMaya
                                </button>
                            </div>
                        </div>
                        <label for="credit-debit">
                        <input id="credit-debit" name="payment" onclick="showPaymentMethod('credit-debit-details')" type="radio"/>
                        Credit or Debit
                        </label>
                        <div class="details cre-det" id="credit-debit-details">
                            <div class="payment-method-content cre" style="background-color: transparent; border:none;">
                                <input class="cardnum" placeholder="Card number" type="text"/>
                                <div class="form-group">
                                    <input placeholder="MM/YY" type="text"/>
                                    <input placeholder="CVV" type="text"/>
                                </div>
                                <input class="cardname" placeholder="Cardholder's name" type="text"/>
                                <div class="form-group">
                                    <input readonly type="text" value="Cebu, Philippines"/>
                                    <input id="city-code" placeholder="6000" type="number"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dont-dis-opt-cont"> 
                    <label class="container-check">Don’t display any of my information on the fundraiser.
                        <input type="checkbox" checked="checked">
                        <span class="checkmark"></span>
                    </label>
                </div>

                <div class="your-withdrawal">
                    <h1>Your Donation</h1>
                    <div class="withdrawal-total-am">
                        <p>Total Amount</p>
                        <p id="totalAmount">₱ 0.00</p>
                    </div>
                </div>
                <div class="withdraw-button">
                    <button class="withdraw-btn" type="submit">Donate Now</button>
                </div>
                <script>
                    const buttons = document.querySelectorAll(".toggleButton");
                    const inputField = document.querySelector(".input-amount-opt input");
                    const totalAmountDisplay = document.querySelector("#totalAmount");

                    const updateTotalAmount = (amount) => {
                        totalAmountDisplay.textContent = `₱ ${parseFloat(amount).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        })}`;
                    };

                    buttons.forEach((button) => {
                        button.addEventListener("click", () => {
                            // Remove the 'active' class from all buttons
                            buttons.forEach(btn => btn.classList.remove("active"));
                            // Add the 'active' class to the clicked button
                            button.classList.add("active");

                            // Get the value from the clicked button and set it in the input field
                            const value = button.getAttribute("data-value");
                            inputField.value = value;

                            updateTotalAmount(value);
                        });
                    });

                    inputField.addEventListener("input", () => {
                        const value = inputField.value;
                        if (value && value >= 0) {
                            updateTotalAmount(value);
                        } else {
                            updateTotalAmount(0);
                        }
                    });
                </script>
            </form>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const paymentStates = {
                    paypal: false,
                    gcash: false,
                    paymaya: false,
                    "credit-debit": false
                };

                // Set the payment state based on the current payment method passed from PHP
                if (currentPaymentMethod) {
                    paymentStates[currentPaymentMethod.toLowerCase()] = true;
                }

                function showPaymentMethod(method) {
                    // Hide all payment details
                    const methods = document.querySelectorAll('.details');
                    methods.forEach(el => (el.style.display = 'none'));

                    // Show the selected payment details
                    const selectedMethod = document.getElementById(method);
                    if (selectedMethod) {
                        selectedMethod.style.display = 'block';
                    }
                }

                function toggleLink(method) {
                    // Unlink all other methods
                    Object.keys(paymentStates).forEach(key => {
                        if (key !== method) {
                            paymentStates[key] = false;
                            const otherButton = document.getElementById(`${key}-link-button`);
                            if (otherButton) {
                                otherButton.textContent = `Link to ${capitalize(key)}`;
                            }
                        }
                    });

                    // Toggle the selected method's linked state
                    paymentStates[method] = !paymentStates[method];

                    // Update the button text for the selected method
                    const button = document.getElementById(`${method}-link-button`);
                    if (button) {
                        if (paymentStates[method]) {
                            button.textContent = `Unlink from ${capitalize(method)}`;
                        } else {
                            button.textContent = `Link to ${capitalize(method)}`;
                        }
                    }
                }

                // Helper function to capitalize the first letter of the method
                function capitalize(text) {
                    return text.charAt(0).toUpperCase() + text.slice(1);
                }

                // Function to initialize the page
                function initializePage() {
                    // Set the pre-selected payment method's button text
                    Object.keys(paymentStates).forEach(method => {
                        const button = document.getElementById(`${method}-link-button`);
                        const radio = document.getElementById(method);
                        if (button) {
                            if (paymentStates[method]) {
                                button.textContent = `Unlink from ${capitalize(method)}`;
                            } else {
                                button.textContent = `Link to ${capitalize(method)}`;
                            }
                        }
                        // Check the radio button for the pre-selected method
                        if (radio) {
                            radio.checked = paymentStates[method];
                        }
                    });

                    // Automatically show the pre-selected payment method's details
                    const preSelected = Object.keys(paymentStates).find(method => paymentStates[method]);
                    if (preSelected) {
                        showPaymentMethod(`${preSelected}-details`);
                    }
                }

                // Attach event listeners to radio buttons
                const radioButtons = document.querySelectorAll('input[name="payment"]');
                radioButtons.forEach(button => {
                    button.addEventListener('change', function () {
                        showPaymentMethod(`${this.id}-details`);
                    });
                });

                // Attach event listeners to toggle link buttons
                document.querySelectorAll('.details button').forEach(button => {
                    const method = button.id.split('-')[0]; // Extract method name from button ID
                    button.addEventListener('click', function () {
                        toggleLink(method);
                    });
                });

                // Initialize the page
                initializePage();
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
