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

    $query = "SELECT payment_method FROM payment_method WHERE proj_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $paymentMethod = "None"; // Default value

    if ($result->num_rows > 0) {
        $method = $result->fetch_assoc();
        $paymentMethod = $method['payment_method'];
    }

    echo "<script>var currentPaymentMethod = '$paymentMethod';</script>";
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $author_icon = $user['author_icon'];
        $authorProfilepic = $user['profile_pic'];

        $country = 'Philippines';
        $city = 'Cebu';
        $author = isset($user['fullname']) ? $user['fullname'] : 'Unknown Author';
        $title = isset($_SESSION['project_title']) ? $_SESSION['project_title'] : 'Untitled Project';
        $category = isset($_SESSION['project_category']) ? $_SESSION['project_category'] : 'General';
        $barangay = isset($_SESSION['barangay']) ? $_SESSION['barangay'] : 'Unknown';
        $fund_goal = isset($_SESSION['fund_goal']) ? $_SESSION['fund_goal'] : 0;
        $end_date = isset($_SESSION['end_date']) ? $_SESSION['end_date'] : '03-22-2025';
        $firstname = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : 'First';
        $lastname = isset($_SESSION['lastname']) ? $_SESSION['lastname'] : 'Last';
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'example@example.com';
        $phonenumber = isset($_SESSION['phone-number']) ? $_SESSION['phone-number'] : '000-000-0000';
        $img = isset($_SESSION['project_img']) ? $_SESSION['project_img'] : 'default.jpg';
        $video_url = isset($_SESSION['video_url']) ? $_SESSION['video_url'] : '';
        $overview = isset($_SESSION['project_overview']) ? $_SESSION['project_overview'] : 'No overview provided.';
        $story = isset($_SESSION['project_story']) ? $_SESSION['project_story'] : 'No story provided.';

        $paymentMethod = isset($_POST['payment']) ? $_POST['payment'] : 'None';

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("UPDATE projects 
                                    SET country = ?, city = ?, author = ?, proj_title = ?, category = ?, barangay = ?, fundgoal = ?, end_date = ?, fname = ?, 
                                    lname = ?, email = ?, phoneno = ?, project_photo = ?, video_url = ?, project_overview = ?, project_story = ?, 
                                    author_icon = ?, profile_pic = ? WHERE proj_id = ?");
            $stmt->bind_param(
                "ssssssssssssssssssi",
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
                $authorProfilepic,
                $project_id
            );

            if (!$stmt->execute()) {
                echo "Error executing update for projects: " . $stmt->error;
                $conn->rollback();
                exit();
            }

            if ($paymentMethod == "PayPal" || $paymentMethod == "GCash" || $paymentMethod == "PayMaya") {
                $stmt = $conn->prepare("UPDATE payment_method SET payment_method = ? WHERE proj_id = ?");
                $stmt->bind_param("si", $paymentMethod, $project_id);
            } elseif ($paymentMethod == "Credit/Debit") {
                $cardNumber = isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : null;
                $expiryDate = isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : null;
                $cvv = isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : null;
                $cardholderName = isset($_POST['cardholder_name']) ? htmlspecialchars($_POST['cardholder_name']) : null;
                $zipCode = isset($_POST['zipcode']) ? htmlspecialchars($_POST['zipcode']) : null;
    
                if (empty($cardNumber) || empty($expiryDate) || empty($cvv) || empty($cardholderName) || empty($zipCode)) {
                    throw new Exception("All credit/debit card fields are required.");
                }
    
                $stmt = $conn->prepare("UPDATE payment_method SET payment_method = ?, card_number = ?, expiration_date = ?, cvv = ?, cardholder_name = ?, zipcode = ? WHERE proj_id = ?");
                $stmt->bind_param(
                    "ssssssi",
                    $paymentMethod,
                    $cardNumber,
                    $expiryDate,
                    $cvv,
                    $cardholderName,
                    $zipCode,
                    $project_id
                );
            } else {
                throw new Exception("Invalid payment method.");
            }
    
            if (!$stmt->execute()) {
                echo "Error executing update for payment method: " . $stmt->error;
                $conn->rollback();
                exit();
            }
            $conn->commit();
    
            echo "<script>alert('Payment information saved successfully.');</script>";
            header('Location: done-edit.php');
    
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['id'] = $idno;
            
        } catch (mysqli_sql_exception $e) {
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
            <a href="lanAfter.html" class="logged-in-nav-item">Home</a>
            <a href="aboutA.html" class="logged-in-nav-item">About</a>
            <a href="projectsA.html" class="logged-in-nav-item">Projects</a>
            <a href="create1.html" class="logged-in-nav-item logged-in-start-project-button">Start a Project</a>
            <div class="logged-in-user-profile">
                <span class="logged-in-user-name">Rosita Quarez</span>
                <img src="../images/profile.png" alt="User Profile" class="logged-in-profile-img">
                <div class="dropdown-menu">
                    <a href="my-projects.html">My Projects</a>
                    <a href="backed-projects.html">Backed Projects</a>
                    <a href="profile-settings.html">Profile Settings</a>
                    <a href="login.html">Log Out</a>
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
                            <span class="step-number step-number-1"><a href="../html/edit2.html" style="text-decoration:none;"><img src="../images/check.svg" alt="Check"></a></span>
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
                            <span class="step-number step-number-1"><a href="../html/edit3.html" style="text-decoration:none;"><img src="../images/check.svg" alt="Check"></a></span>
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
            <form action="edit4.php?proj_id=<?php echo htmlspecialchars($project_id) ?>" method="POST">
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
