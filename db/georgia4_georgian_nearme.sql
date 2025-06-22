-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 22, 2025 at 11:01 AM
-- Server version: 10.6.22-MariaDB-cll-lve
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `georgia4_georgian_nearme`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

CREATE TABLE `api_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `api_provider` varchar(50) NOT NULL COMMENT 'DataForSEO, Google, etc.',
  `endpoint` varchar(255) NOT NULL COMMENT 'API endpoint URL',
  `request_method` varchar(10) NOT NULL DEFAULT 'GET',
  `request_data` longtext DEFAULT NULL COMMENT 'Request payload as JSON',
  `response_data` longtext DEFAULT NULL COMMENT 'Response data as JSON',
  `response_code` int(11) NOT NULL COMMENT 'HTTP response code',
  `response_time` decimal(8,4) DEFAULT NULL COMMENT 'Response time in seconds',
  `api_cost` decimal(8,4) DEFAULT NULL COMMENT 'API call cost',
  `user_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API request logging for monitoring and debugging';

-- --------------------------------------------------------

--
-- Table structure for table `attribute_definitions`
--

CREATE TABLE `attribute_definitions` (
  `id` int(11) UNSIGNED NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'USA',
  `slug` varchar(150) DEFAULT NULL,
  `seo_url` varchar(200) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `last_api_sync` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `original_title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `seo_url` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_borough` varchar(100) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_zip` varchar(20) DEFAULT NULL,
  `address_region` varchar(100) DEFAULT NULL,
  `address_country_code` varchar(2) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `google_place_id` varchar(100) DEFAULT NULL,
  `cid` varchar(50) DEFAULT NULL,
  `feature_id` varchar(100) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `rating_count` int(11) DEFAULT 0,
  `rating_type` varchar(10) DEFAULT 'Max5',
  `rating_distribution` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rating_distribution`)),
  `price_level` varchar(20) DEFAULT NULL,
  `current_status` enum('open','closed','temporarily_closed','permanently_closed') DEFAULT 'open',
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `category_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_ids`)),
  `additional_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_categories`)),
  `website` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `main_image_url` varchar(500) DEFAULT NULL,
  `total_photos` int(11) DEFAULT 0,
  `snippet` text DEFAULT NULL,
  `hours` text DEFAULT NULL,
  `work_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`work_hours`)),
  `popular_times` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`popular_times`)),
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `service_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_options`)),
  `accessibility_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`accessibility_options`)),
  `dining_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dining_options`)),
  `atmosphere` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`atmosphere`)),
  `crowd_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`crowd_info`)),
  `payment_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_options`)),
  `people_also_search` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`people_also_search`)),
  `place_topics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`place_topics`)),
  `business_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`business_links`)),
  `check_url` varchar(500) DEFAULT NULL,
  `data_source` varchar(50) DEFAULT 'Manual',
  `last_updated_api` timestamp NULL DEFAULT NULL,
  `first_seen_api` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `restaurants_with_attributes`
-- (See below for the actual view)
--
CREATE TABLE `restaurants_with_attributes` (
`id` int(11)
,`city_id` int(11)
,`name` varchar(200)
,`original_title` varchar(255)
,`slug` varchar(255)
,`seo_url` varchar(255)
,`address` text
,`address_borough` varchar(100)
,`address_city` varchar(100)
,`address_zip` varchar(20)
,`address_region` varchar(100)
,`address_country_code` varchar(2)
,`latitude` decimal(10,8)
,`longitude` decimal(11,8)
,`phone` varchar(20)
,`google_place_id` varchar(100)
,`cid` varchar(50)
,`feature_id` varchar(100)
,`rating` decimal(2,1)
,`rating_count` int(11)
,`rating_type` varchar(10)
,`rating_distribution` longtext
,`price_level` varchar(20)
,`current_status` enum('open','closed','temporarily_closed','permanently_closed')
,`description` text
,`category` varchar(100)
,`category_ids` longtext
,`additional_categories` longtext
,`website` varchar(255)
,`domain` varchar(255)
,`logo_url` varchar(500)
,`main_image_url` varchar(500)
,`total_photos` int(11)
,`snippet` text
,`hours` text
,`work_hours` longtext
,`popular_times` longtext
,`attributes` longtext
,`service_options` longtext
,`accessibility_options` longtext
,`dining_options` longtext
,`atmosphere` longtext
,`crowd_info` longtext
,`payment_options` longtext
,`people_also_search` longtext
,`place_topics` longtext
,`business_links` longtext
,`check_url` varchar(500)
,`data_source` varchar(50)
,`last_updated_api` timestamp
,`first_seen_api` timestamp
,`is_active` tinyint(1)
,`created_at` timestamp
,`updated_at` timestamp
,`all_attributes` mediumtext
,`available_features` mediumtext
);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_attributes`
--

CREATE TABLE `restaurant_attributes` (
  `id` int(11) UNSIGNED NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `attribute_category` varchar(50) NOT NULL,
  `attribute_name` varchar(100) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_hours`
--

CREATE TABLE `restaurant_hours` (
  `id` int(11) UNSIGNED NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_closed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_photos`
--

CREATE TABLE `restaurant_photos` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `photo_reference` varchar(255) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_popular_times`
--

CREATE TABLE `restaurant_popular_times` (
  `id` int(11) UNSIGNED NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL,
  `hour` tinyint(2) NOT NULL COMMENT '0-23',
  `popularity_index` tinyint(3) NOT NULL COMMENT '0-100'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_relations`
--

CREATE TABLE `restaurant_relations` (
  `id` int(11) UNSIGNED NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `related_cid` varchar(50) NOT NULL,
  `related_name` varchar(255) NOT NULL,
  `related_rating` decimal(3,2) DEFAULT NULL,
  `related_rating_count` int(11) DEFAULT NULL,
  `relation_type` varchar(20) DEFAULT 'people_also_search'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `restaurant_summary`
-- (See below for the actual view)
--
CREATE TABLE `restaurant_summary` (
`id` int(11)
,`name` varchar(200)
,`category` varchar(100)
,`address_city` varchar(100)
,`rating` decimal(2,1)
,`rating_count` int(11)
,`price_level` varchar(20)
,`current_status` enum('open','closed','temporarily_closed','permanently_closed')
,`phone` varchar(20)
,`website` varchar(255)
,`is_active` tinyint(1)
,`attributes_count` bigint(21)
,`available_attributes` mediumtext
);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_topics`
--

CREATE TABLE `restaurant_topics` (
  `id` int(11) UNSIGNED NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `topic` varchar(100) NOT NULL,
  `mention_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_requests`
--

CREATE TABLE `search_requests` (
  `id` int(11) NOT NULL,
  `search_query` varchar(255) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_logs`
--
ALTER TABLE `api_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_api_provider` (`api_provider`),
  ADD KEY `idx_endpoint` (`endpoint`),
  ADD KEY `idx_response_code` (`response_code`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_provider_date` (`api_provider`,`created_at`),
  ADD KEY `idx_stats_lookup` (`api_provider`,`created_at`,`response_code`),
  ADD KEY `idx_cost_analysis` (`api_provider`,`api_cost`,`created_at`);

--
-- Indexes for table `attribute_definitions`
--
ALTER TABLE `attribute_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_category_name` (`category`,`name`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_place_id` (`google_place_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_restaurants_city_slug` (`city_id`,`slug`),
  ADD KEY `idx_cid` (`cid`),
  ADD KEY `idx_feature_id` (`feature_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_rating_count` (`rating`,`rating_count`),
  ADD KEY `idx_city` (`address_city`),
  ADD KEY `idx_status` (`current_status`,`is_active`);

--
-- Indexes for table `restaurant_attributes`
--
ALTER TABLE `restaurant_attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_restaurant_attribute` (`restaurant_id`,`attribute_category`,`attribute_name`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`),
  ADD KEY `idx_category` (`attribute_category`),
  ADD KEY `idx_name` (`attribute_name`);

--
-- Indexes for table `restaurant_hours`
--
ALTER TABLE `restaurant_hours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_restaurant_day` (`restaurant_id`,`day_of_week`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`),
  ADD KEY `idx_day` (`day_of_week`);

--
-- Indexes for table `restaurant_photos`
--
ALTER TABLE `restaurant_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_photo_reference` (`photo_reference`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_restaurant_main` (`restaurant_id`,`is_primary`);

--
-- Indexes for table `restaurant_popular_times`
--
ALTER TABLE `restaurant_popular_times`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_restaurant_day_hour` (`restaurant_id`,`day_of_week`,`hour`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`);

--
-- Indexes for table `restaurant_relations`
--
ALTER TABLE `restaurant_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`),
  ADD KEY `idx_related_cid` (`related_cid`);

--
-- Indexes for table `restaurant_topics`
--
ALTER TABLE `restaurant_topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_restaurant_topic` (`restaurant_id`,`topic`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`),
  ADD KEY `idx_topic` (`topic`);

--
-- Indexes for table `search_requests`
--
ALTER TABLE `search_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_logs`
--
ALTER TABLE `api_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attribute_definitions`
--
ALTER TABLE `attribute_definitions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_attributes`
--
ALTER TABLE `restaurant_attributes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_hours`
--
ALTER TABLE `restaurant_hours`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_photos`
--
ALTER TABLE `restaurant_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_popular_times`
--
ALTER TABLE `restaurant_popular_times`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_relations`
--
ALTER TABLE `restaurant_relations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_topics`
--
ALTER TABLE `restaurant_topics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_requests`
--
ALTER TABLE `search_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `restaurants_with_attributes`
--
DROP TABLE IF EXISTS `restaurants_with_attributes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `restaurants_with_attributes`  AS SELECT `r`.`id` AS `id`, `r`.`city_id` AS `city_id`, `r`.`name` AS `name`, `r`.`original_title` AS `original_title`, `r`.`slug` AS `slug`, `r`.`seo_url` AS `seo_url`, `r`.`address` AS `address`, `r`.`address_borough` AS `address_borough`, `r`.`address_city` AS `address_city`, `r`.`address_zip` AS `address_zip`, `r`.`address_region` AS `address_region`, `r`.`address_country_code` AS `address_country_code`, `r`.`latitude` AS `latitude`, `r`.`longitude` AS `longitude`, `r`.`phone` AS `phone`, `r`.`google_place_id` AS `google_place_id`, `r`.`cid` AS `cid`, `r`.`feature_id` AS `feature_id`, `r`.`rating` AS `rating`, `r`.`rating_count` AS `rating_count`, `r`.`rating_type` AS `rating_type`, `r`.`rating_distribution` AS `rating_distribution`, `r`.`price_level` AS `price_level`, `r`.`current_status` AS `current_status`, `r`.`description` AS `description`, `r`.`category` AS `category`, `r`.`category_ids` AS `category_ids`, `r`.`additional_categories` AS `additional_categories`, `r`.`website` AS `website`, `r`.`domain` AS `domain`, `r`.`logo_url` AS `logo_url`, `r`.`main_image_url` AS `main_image_url`, `r`.`total_photos` AS `total_photos`, `r`.`snippet` AS `snippet`, `r`.`hours` AS `hours`, `r`.`work_hours` AS `work_hours`, `r`.`popular_times` AS `popular_times`, `r`.`attributes` AS `attributes`, `r`.`service_options` AS `service_options`, `r`.`accessibility_options` AS `accessibility_options`, `r`.`dining_options` AS `dining_options`, `r`.`atmosphere` AS `atmosphere`, `r`.`crowd_info` AS `crowd_info`, `r`.`payment_options` AS `payment_options`, `r`.`people_also_search` AS `people_also_search`, `r`.`place_topics` AS `place_topics`, `r`.`business_links` AS `business_links`, `r`.`check_url` AS `check_url`, `r`.`data_source` AS `data_source`, `r`.`last_updated_api` AS `last_updated_api`, `r`.`first_seen_api` AS `first_seen_api`, `r`.`is_active` AS `is_active`, `r`.`created_at` AS `created_at`, `r`.`updated_at` AS `updated_at`, group_concat(distinct concat(`ra`.`attribute_category`,':',`ra`.`attribute_name`) order by `ra`.`attribute_category` ASC,`ra`.`attribute_name` ASC separator '|') AS `all_attributes`, group_concat(distinct case when `ra`.`is_available` = 1 then `ra`.`attribute_name` end order by `ra`.`attribute_name` ASC separator ', ') AS `available_features` FROM (`restaurants` `r` left join `restaurant_attributes` `ra` on(`r`.`id` = `ra`.`restaurant_id`)) WHERE `r`.`is_active` = 1 GROUP BY `r`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `restaurant_summary`
--
DROP TABLE IF EXISTS `restaurant_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `restaurant_summary`  AS SELECT `r`.`id` AS `id`, `r`.`name` AS `name`, `r`.`category` AS `category`, `r`.`address_city` AS `address_city`, `r`.`rating` AS `rating`, `r`.`rating_count` AS `rating_count`, `r`.`price_level` AS `price_level`, `r`.`current_status` AS `current_status`, `r`.`phone` AS `phone`, `r`.`website` AS `website`, `r`.`is_active` AS `is_active`, count(`ra`.`id`) AS `attributes_count`, group_concat(distinct `ra`.`attribute_name` order by `ra`.`attribute_name` ASC separator ', ') AS `available_attributes` FROM (`restaurants` `r` left join `restaurant_attributes` `ra` on(`r`.`id` = `ra`.`restaurant_id` and `ra`.`is_available` = 1)) WHERE `r`.`is_active` = 1 GROUP BY `r`.`id` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
