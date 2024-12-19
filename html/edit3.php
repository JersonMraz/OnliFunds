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
        $_SESSION['project_overview'] = $_POST['project-overview'];
        $_SESSION['project_story'] = $_POST['project-story'];
        header('Location: edit4.php?proj_id='. $project_id); // Redirect to the next page
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
		.line-1 {
		 height: 20px;
		 }
		.line-2 {
			height: 40px; /* Height of the vertical line */
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
                            <span class="step-number step-number-1"><img src="../images/pen.png" alt="Pen"></span>
                            <div class="line line-2"></div> <!-- Line under step 3 -->
                        </div>
                        <div class="step-text">
                            <span class="text text-3">Story</span>
                            <span class="description description-1">Add the project overview and the project story of why you’re fundraising.</span>
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
        <div class="main-content3">
            <form action="" id="formArea" method="post">
                <h2>Fundraiser Story</h2>
                
                <h3>Project Overview</h3>
                <p>Give a short description of what your fundraising for.</p>

                <textarea id="myTextarea" form="formArea" rows="4" cols="50" placeholder="Type here..." name="project-overview"><?php echo htmlspecialchars($project['project_overview']) ?></textarea>

                <h3>Project Story</h3>
                <p>Explain why you're raising the money, what are the funds used for, and how much you value the support.</p>
                <div class="editor">
                    <div class="toolbar">
                        <button onclick="toggleBold()"><i class="fas fa-bold"></i></button>
                        <button onclick="toggleItalic()"><i class="fas fa-italic"></i></button>
                        <button onclick="toggleUnderline()"><i class="fas fa-underline"></i></button>
                        <button onclick="alignLeft()"><i class="fas fa-align-left"></i></button>
                        <button onclick="alignCenter()"><i class="fas fa-align-center"></i></button>
                        <button onclick="alignRight()"><i class="fas fa-align-right"></i></button>
                        <button onclick="alignJustify()"><i class="fas fa-align-justify"></i></button>
                        <button onclick="insertUnorderedList()"><i class="fas fa-list-ul"></i></button>
                        <button onclick="insertOrderedList()"><i class="fas fa-list-ol"></i></button>
                        <button onclick="insertImage()"><i class="fas fa-image"></i></button>
                        <button onclick="insertVideo()"><i class="fas fa-video"></i></button>
                    </div>
                    
                    <!-- The editable area -->
                    <textarea id="editor" form="formArea" name="project-story" contenteditable="true" style="padding: 10px; min-height: 200px;"><?php echo htmlspecialchars($project['project_story']) ?></textarea>

                    <!-- File input for uploading image -->
                    <input type="file" id="imageUpload" accept="image/*" style="display:none;" onchange="handleImageUpload(event)" />
                    <input type="file" id="videoUpload" accept="video/*" style="display:none;" onchange="handleVideoUpload(event)" />
                </div>
                <button class="continue-btn" type="submit">Continue</button>
            </form>
        </div>
        <script>
        // Function to handle bold text
        function toggleBold() {
            document.execCommand('bold', false, null);
        }

        // Function to handle italic text
        function toggleItalic() {
            document.execCommand('italic', false, null);
        }

        // Function to handle underline text
        function toggleUnderline() {
            document.execCommand('underline', false, null);
        }

        // Function to align text left
        function alignLeft() {
            document.execCommand('justifyLeft', false, null);
        }

        // Function to align text center
        function alignCenter() {
            document.execCommand('justifyCenter', false, null);
        }

        // Function to align text right
        function alignRight() {
            document.execCommand('justifyRight', false, null);
        }

        // Function to justify text
        function alignJustify() {
            document.execCommand('justifyFull', false, null);
        }

        // Function to insert an unordered list
        function insertUnorderedList() {
            document.execCommand('insertUnorderedList', false, null);
        }

        // Function to insert an ordered list
        function insertOrderedList() {
            document.execCommand('insertOrderedList', false, null);
        }

        // Function to insert an image from local storage (Base64)
        function insertImage() {
            document.getElementById("imageUpload").click();
        }

        // Function to handle image upload and convert it to Base64
        function handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onloadend = function() {
                const base64Image = reader.result;
                const editor = document.getElementById("editor");
                const imgTag = `<img src="${base64Image}" alt="Image" style="max-width:100%; height:auto;">`;
                document.execCommand('insertHTML', false, imgTag);
            };

            reader.readAsDataURL(file);
        }
        </script>
        <script>
            // Extract YouTube Video ID
            function getYouTubeVideoId(url) {
                const regex = /(?:https?:\/\/(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/\S+|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/))([A-Za-z0-9_-]{11})/;
                const match = url.match(regex);
                return match ? match[1] : null;
            }

            // Extract Vimeo Video ID
            function getVimeoVideoId(url) {
                const regex = /(?:https?:\/\/(?:www\.)?vimeo\.com\/(\d+))/;
                const match = url.match(regex);
                return match ? match[1] : null;
            }

            // Extract Instagram Post ID
            function getInstagramPostId(url) {
                const regex = /https:\/\/www.instagram.com\/p\/([a-zA-Z0-9_-]+)\//;
                const match = url.match(regex);
                return match ? match[1] : null;
            }

            // Extract Facebook Video ID
            function getFacebookVideoId(url) {
                const regex = /(?:https:\/\/www\.facebook\.com\/(?:[^\/\n\s]+\/\S+\/\S+|(?:video\.php\?v=))(\d+))/;
                const match = url.match(regex);
                return match ? match[1] : null;
            }

            // Extract Twitter Tweet ID
            function getTwitterTweetId(url) {
                const regex = /twitter\.com\/(?:[^\/\n\s]+)\/status\/(\d+)/;
                const match = url.match(regex);
                return match ? match[1] : null;
            }

            // Video Insert Function
            function insertVideo() {
                const url = prompt("Enter the video URL (YouTube, Vimeo, Instagram, Facebook, Twitter, etc.):");
                if (url) {
                    let embedCode = "";
                    
                    // YouTube embed URL conversion
                    if (url.includes("youtube.com") || url.includes("youtu.be")) {
                        const videoId = getYouTubeVideoId(url);
                        if (videoId) {
                            embedCode = `<iframe src="https://www.youtube.com/embed/${videoId}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
                        } else {
                            alert("Invalid YouTube URL.");
                        }
                    }
                    // Vimeo embed URL conversion
                    else if (url.includes("vimeo.com")) {
                        const videoId = getVimeoVideoId(url);
                        if (videoId) {
                            embedCode = `<iframe src="https://player.vimeo.com/video/${videoId}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
                        } else {
                            alert("Invalid Vimeo URL.");
                        }
                    }
                    // Instagram embed URL conversion (Requires Instagram post URL)
                    else if (url.includes("instagram.com")) {
                        const postId = getInstagramPostId(url);
                        if (postId) {
                            embedCode = `<iframe src="https://www.instagram.com/p/${postId}/embed" width="500" height="600" frameborder="0" scrolling="no" allowtransparency="true"></iframe>`;
                        } else {
                            alert("Invalid Instagram URL.");
                        }
                    }
                    // Facebook Video embed URL conversion
                    else if (url.includes("facebook.com")) {
                        const videoId = getFacebookVideoId(url);
                        if (videoId) {
                            embedCode = `<iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fvideo.php%3Fv%3D${videoId}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
                        } else {
                            alert("Invalid Facebook URL.");
                        }
                    }
                    // Twitter video embed
                    else if (url.includes("twitter.com")) {
                        const tweetId = getTwitterTweetId(url);
                        if (tweetId) {
                            embedCode = `<blockquote class="twitter-tweet"><a href="https://twitter.com/user/status/${tweetId}"></a></blockquote>`;
                            // Load Twitter Embed script
                            (function(d, s, id) {
                                var js, fjs = d.getElementsByTagName(s)[0];
                                if (d.getElementById(id)) return;
                                js = d.createElement(s); js.id = id;
                                js.src = "https://platform.twitter.com/widgets.js";
                                fjs.parentNode.insertBefore(js, fjs);
                            }(document, "script", "twitter-wjs"));
                        } else {
                            alert("Invalid Twitter URL.");
                        }
                    }
                    // Direct Video URL (any video URL that supports iframe embedding)
                    else {
                        embedCode = `<iframe src="${url}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
                    }

                    // Insert the video embed code
                    if (embedCode) {
                        try {
                            document.execCommand('insertHTML', false, embedCode);
                        } catch (e) {
                            alert("Failed to insert video. The video platform might block embedding.");
                        }
                    }
                }
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
