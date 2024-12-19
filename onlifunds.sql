-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 19, 2024 at 03:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `onlifunds`
--

-- --------------------------------------------------------

--
-- Table structure for table `donation`
--

CREATE TABLE `donation` (
  `user_id` int(11) NOT NULL,
  `proj_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `donation_amount` int(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation`
--

INSERT INTO `donation` (`user_id`, `proj_id`, `payment_method`, `donation_amount`) VALUES
(1, 5, 'on', 1000),
(1, 10, 'on', 1000),
(1, 8, 'on', 3500);

-- --------------------------------------------------------

--
-- Table structure for table `payment_method`
--

CREATE TABLE `payment_method` (
  `proj_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `card_number` varchar(50) NOT NULL,
  `expiration_date` varchar(50) NOT NULL,
  `cvv` varchar(50) NOT NULL,
  `cardholder_name` varchar(50) NOT NULL,
  `zipcode` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_method`
--

INSERT INTO `payment_method` (`proj_id`, `payment_method`, `card_number`, `expiration_date`, `cvv`, `cardholder_name`, `zipcode`) VALUES
(0, 'PayPal', '', '', '', '', ''),
(5, 'Credit/Debit', '1234-1234-1234-1234', '03/25', '123', 'Jerson Sullano', '6000'),
(10, 'Credit/Debit', '1234-4567-1234-1289', '12/25', '123', 'Diosana, Poland', '6000');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `user_id` int(11) NOT NULL,
  `proj_id` int(11) NOT NULL,
  `country` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `author` varchar(50) NOT NULL,
  `proj_title` varchar(100) NOT NULL,
  `category` varchar(255) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `fundgoal` varchar(50) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `end_date` varchar(255) NOT NULL,
  `fname` varchar(25) NOT NULL,
  `lname` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phoneno` bigint(12) NOT NULL,
  `project_photo` varchar(50) NOT NULL,
  `video_url` varchar(50) NOT NULL,
  `project_overview` text NOT NULL,
  `project_story` text NOT NULL,
  `author_icon` varchar(50) NOT NULL,
  `profile_pic` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`user_id`, `proj_id`, `country`, `city`, `author`, `proj_title`, `category`, `barangay`, `fundgoal`, `created_at`, `end_date`, `fname`, `lname`, `email`, `phoneno`, `project_photo`, `video_url`, `project_overview`, `project_story`, `author_icon`, `profile_pic`) VALUES
(1, 5, 'Philippines', 'Cebu', 'Jerson Sullano', 'Remote of the Future: Revolutionizing Technology', 'Technology', 'Mambaling', '8,000', '2024-12-19', '2025-01-30', 'Harold', 'Nabua', 'haroldnabua@email.com', 9123456789, 'image 75.png', 'http://Techno.com', '\"Remote of the Future: Revolutionizing Technology\" delves into the evolution of remote work powered by cutting-edge technologies. It investigates how advancements like artificial intelligence, cloud platforms, augmented reality, and 5G networks are transforming traditional workplaces into flexible, connected environments. The project highlights the potential for these innovations to enhance productivity, streamline communication, and create a more inclusive and effeicient global workforce. ', 'Remote of the Future: Revolutionizing Technology\r\n\r\nOverview\r\nIn the ever-evolving world of technology, the remote control is set to undergo a radical transformation. \"Remote of the Future\" aims to revolutionize how we interact with devices by merging cutting-edge technology with intuitive design to create a seamlesss, multifunctional remote that transcends the limitations of traditional controllers. With advancements in artificial intelligence (AI), voice recognition, and smart connectivity, this next-generation remote promises to enhance user experience, making it smarter, more responsive, and more versatile than ever before.\r\n\r\nFeatures and Capabilities\r\n\r\nVoice Control Integration: The Remote of the Future will feature advanced voice recognition technology, allowing users to control their devices through natural language commands. Whether it’s adjusting the volume, changing channels, or controlling smart home appliances, users can simply speak their requests, and the remote will respond instantly.\r\n\r\nMulti-Device Control: Gone are the days of juggling multiple remotes for different devices. This remote can connect with and control various smart devices, including televisions, streaming devices, sound systems, smart thermostats, lighting, and even security systems. Its universal functionality means you can manage your entire home ecosystem from a single device.\r\n', 'JS', 'jerson.jpg'),
(2, 8, 'Philippines', 'Cebu', 'Clifford Alferez', 'Help Us Bring Life-Saving Treatments to Those in Need', 'Health', 'Kawasan', '13,500', '2024-12-19', '2025-01-10', 'Simon', 'Sebios', 'simon@email.com', 9123456789, 'health23.png', 'http://health.com', 'Join us in our mission to provide access to life-saving treatments for those who need it most. Many communities around the world lack essential healthcare services, leaving vulnerable individuals without the support they deserve. This campaign aims to bridge that gap by funding critical medical supplies, treatments, and resources for underserved populations. With your support, we can bring hope and healing to countless lives, improving health outcomes and empowering communities to thrive.', '\"Bringing Life-Saving Healthcare to Those in Need: A Mission of Hope and Healing\"\r\nIn many parts of the world, basic healthcare is still a privilege rather than a universal right. Vulnerable communities, especially those in remote or underserved areas, often face an overwhelming lack of access to essential treatments and medical resources. People are left without life-saving interventions, and families are devastated by preventable diseases and untreated conditions. This project is dedicated to changing that reality—one person, one family, and one community at a time.\r\nWhy This Project Matters\r\nImagine facing a life-threatening illness and having nowhere to turn. In rural communities, people suffering from common but serious health issues often lack nearby hospitals, trained medical staff, and even basic medications. This campaign aims to address these barriers by providing essential healthcare support where it’s needed most. Our goal is not only to save lives but also to empower local healthcare providers and create sustainable improvements in community health.\r\nWhat We’re Funding\r\nYour generous contributions will go directly toward:\r\nMedical Supplies: Stocking health centers with vital supplies such as antibiotics, IV fluids, wound care materials, and equipment for critical care.\r\nMobile Clinics: Setting up mobile units to reach remote areas, bringing doctors, nurses, and medical volunteers to communities that would otherwise go without care.\r\nTraining and Empowerment: Training local healthcare workers so they can continue to provide quality care long after the campaign ends. By building local capacity, we ensure that our efforts have a lasting impact.\r\nHealth Education: Providing essential health education to families, helping them understand and prevent common illnesses. Educated communities are healthier communities, as people learn how to avoid and manage diseases effectively.\r\n\r\n', 'CA', 'clifford.jpg'),
(1, 10, 'Philippines', 'Cebu', 'Jerson Sullano', 'Siomai for a Cause: Partnering for Growth ', 'Business', 'Pardo', '2,350', '2024-12-19', '2024-12-30', 'Polando', 'Nabua', 'polando@test.com', 9123456789, 'image 77.png', 'http://SiomaiBusiness.ph/', '\"Siomai for a Cause: Partnering for Growth\" is a community-driven initiative that combines the love for delicious siomai with the power of social responsibility. By partnering with local businesses and organizations, this campaign aims to provide financial support to communities in need, creating opportunities for growth and development while promoting the joy of good food.\r\n\r\n', 'Siomai for a Cause: Partnering for Growth\r\n\"Siomai for a Cause: Partnering for Growth\" is an innovative and heartwarming initiative that brings together the community through the love of food, specifically siomai, to raise funds and create lasting positive change. This campaign seeks to provide support for local communities by creating opportunities for growth, development, and empowerment—all while celebrating the flavors that bring us together.\r\n\r\nOur Mission\r\nAt the heart of this initiative is the belief that food can be a powerful tool for social good. \"Siomai for a Cause\" aims to raise funds through the sale of locally made siomai, with all proceeds going to support community development projects, educational programs, and healthcare initiatives for those in need. By partnering with local vendors, businesses, and organizations, we strive to promote both culinary delight and social responsibility.\r\nWhy It Matters\r\nMany communities, especially those in underserved areas, struggle with access to basic necessities such as education, healthcare, and livelihood opportunities. With your help, we can change that. By supporting this campaign, you are not only enjoying delicious siomai but also contributing to initiatives that will improve the lives of those who need it the most.\r\nFood is more than just nourishment; it’s a way to bring people together, strengthen connections, and support important causes. Through \"Siomai for a Cause,\" we can transform simple meals into vehicles for positive change, creating a ripple effect of growth and empowerment in the communities we serve.\r\n\r\nSiomai Sales: Local siomai vendors will be selling their delicious creations, with a portion of every sale going towards funding key community projects.\r\nCollaborations and Partnerships: By partnering with businesses, schools, and organizations, we aim to create awareness, increase engagement, and amplify the reach of this cause.\r\nCommunity Projects: The funds raised will go directly into supporting educational scholarships, medical assistance programs, livelihood training, and other community development initiatives.\r\nY', 'JS', 'jerson.jpg'),
(1, 15, 'Philippines', 'Cebu', 'Jerson Sullano', 'Crowd Funding for Alferez', 'Business', 'Pahina Central', '10,000', '2024-12-19', '2024-12-25', 'Clifford', 'Alferez', 'cliffordalferez@gmail.com', 9123456789, 'banner1.png', 'http://Youtube.com', 'Alferez dance in FORLAN', 'Alferez dance in FORLAN', 'JS', 'jerson.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `isAdmin` enum('1','0') NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `profile_pic` varchar(50) NOT NULL,
  `phoneno` bigint(12) NOT NULL,
  `barangay` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `isAdmin`, `email`, `password`, `firstname`, `lastname`, `profile_pic`, `phoneno`, `barangay`) VALUES
(1, '', 'jerson@gmail.com', '123', 'Jerson', 'Sullano', 'jerson.jpg', 9474651751, 'Sapangdaku'),
(2, '', 'clifford@gmail.com', '123', 'Clifford', 'Alferez', 'clifford.jpg', 9123456789, 'Duljo'),
(3, '', 'jomari@gmail.com', '123', 'Jomari', 'Marson', '', 0, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `donation`
--
ALTER TABLE `donation`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `proj_id` (`proj_id`);

--
-- Indexes for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`proj_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`proj_id`),
  ADD KEY `UserID` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `proj_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donation`
--
ALTER TABLE `donation`
  ADD CONSTRAINT `donation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donation_ibfk_2` FOREIGN KEY (`proj_id`) REFERENCES `projects` (`proj_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `UserID` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
