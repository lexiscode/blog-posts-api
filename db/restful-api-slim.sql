-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2023 at 04:16 PM
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
(4, 'programming', 'This is where we post all things programming related.'),
(5, 'movie', 'This is where we post all things movie related.');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumbnail` longtext DEFAULT NULL,
  `author` varchar(255) NOT NULL,
  `posted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `thumbnail`, `author`, `posted_at`) VALUES
(28, 'Why The PHP Hate?', 'why-the-php-hate', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed commodo vel lorem nec fringilla. Nulla facilisi. Nullam tincidunt erat eget scelerisque. Nunc ac lectus sit amet arcu vestibulum sollicitudin nec at dui. Fusce convallis lorem nec ligula feugiat, ac hendrerit quam aliquam.', 'http://localhost:200/thumbnails/64d58d986146d.png', 'Anthia Chinyere', '2023-08-11 02:23:36'),
(37, 'Why The JavaScript Hate?', 'why-the-javascript-hate', 'Sed commodo vel lorem nec fringilla. Nulla facilisi. Nullam tincidunt erat eget scelerisque. Nunc ac lectus sit amet arcu vestibulum sol.', 'http://localhost:200/thumbnails/64f32e125db4c.png', 'chi chi', '2023-09-02 13:44:02'),
(38, 'Why The Java Hate?', 'why-the-java-hate', 'Sed commodo vel lorem nec fringilla. Nulla facilisi. Nullam tincidunt erat eget scelerisque. Nunc ac lectus sit amet arcu vestibulum sol.', 'http://localhost:200/thumbnails/64f32e7f0b335.png', 'Raul Krivin', '2023-09-02 13:45:51'),
(39, 'Why The Java Hate?', 'why-the-java-hate', 'Sed commodo vel lorem nec fringilla. Nulla facilisi. Nullam tincidunt erat eget scelerisque. Nunc ac lectus sit amet arcu vestibulum sol.', 'http://localhost:200/thumbnails/64f32ed39560d.png', 'Hennadii Schvedko', '2023-09-02 13:47:15'),
(40, 'Why The Ruby Hate?', 'why-the-ruby-hate', 'Sed commodo vel lorem nec fringilla. Nulla facilisi. Nullam tincidunt erat eget scelerisque. Nunc ac lectus sit amet arcu vestibulum sol.', 'http://localhost:200/thumbnails/64f32f612c37b.png', 'Elena Chifeac', '2023-09-02 13:49:37');

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
(28, 3),
(37, 4),
(37, 5),
(38, 4),
(38, 5),
(39, 1),
(39, 3),
(39, 5),
(40, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`) VALUES
(1, 'nwokoriealex20@gmail.com', '$2y$10$GBlyOs.DTErxlmKX4rbEzOh6zEHAUXsjZEflPs1Vc9YrmujeZf0.O'),
(5, 'alexis@yahoo.com', '$2y$10$ZTQ1bhCHzpX0c4r2RqlyHOnlHH7SGZsHBMF.xev4VgPZ8Wju4Q/Ku'),
(6, 'test@gmail.com', '$2y$10$C62Gus23eIDNegDpMToCFe2F6Ec0COEtr.1JxF4GkEzMqsav2DkW6'),
(7, 'testest@gmail.com', '$2y$10$H750a5ewQdjaAcYuZR9DqOvyC3/0d2HoIlCbr2gYzU7LhWTRN9uRm'),
(8, 'new_user@example.com', '$2y$10$OZEa5.Rcw10IWkrLWRRstOWqkfPS/9kVDT/U2kwVo1R.slttvJGDm'),
(9, 'chinyere@gmail.com', '$2y$10$4DZJYXnmzR7gI.CFRhlkVekY/7zhG3xzKQBfPA0LjZ8ckz74WBp9i');

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
