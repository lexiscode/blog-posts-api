-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2023 at 04:51 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restful-api-slim`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'news', 'This is where we release news on a daily basis'),
(2, 'music', 'This is where we announce the lastest songs trending.'),
(3, 'goodnews', 'This is the updates on the Bible verses.'),
(4, 'programming', 'This is where we post all things programming related.');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `thumbnail` longtext DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `posted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `thumbnail`, `author`, `posted_at`) VALUES
(1, 'Why The Python Hate?', 'why-the-python-hate', 'God is great and mighty, the creator of the universe.', '{{thumbnailImage}}', 'Alexis Chinyere', '2023-08-09 01:40:02'),
(2, 'Why The C Hate?', 'why-the-c-hate', 'God is great and mighty, the creator of the universe.', 'an image here', 'Alexis Chinyere', '2023-08-09 01:40:21'),
(3, 'Update Post Title', 'update-post-title', 'This is the updated content of the post.', 'updated-image-url.jpg', 'Jane Doe', '2023-08-09 01:40:26'),
(7, 'Updated Post Title', 'updated-post-title', 'This is the updated content of the post.', 'updated-image-url.jpg', 'Jane Doe', '2023-08-09 02:56:36'),
(25, 'Why The JavaScript Hate?', 'why-the-python-hate', 'God is great and mighty, the creator of the universe.', 'http://localhost:200/thumbnails/64d43ac222861.png', 'Alexis Chinyere', '2023-08-10 02:17:54');

-- --------------------------------------------------------

--
-- Table structure for table `posts_categories`
--

CREATE TABLE `posts_categories` (
  `post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts_categories`
--

INSERT INTO `posts_categories` (`post_id`, `category_id`) VALUES
(2, 2),
(2, 3),
(3, 1),
(7, 1),
(7, 2),
(25, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts_categories`
--
ALTER TABLE `posts_categories`
  ADD PRIMARY KEY (`post_id`,`category_id`),
  ADD KEY `posts_categories_ibfk_2` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts_categories`
--
ALTER TABLE `posts_categories`
  ADD CONSTRAINT `posts_categories_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
