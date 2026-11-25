-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2026 at 06:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Database: `agromarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_audit_logs`
--

CREATE TABLE `admin_audit_logs` (
    `log_id` bigint(20) UNSIGNED NOT NULL,
    `admin_id` bigint(20) UNSIGNED NOT NULL,
    `action` varchar(100) NOT NULL,
    `target_type` varchar(50) NOT NULL,
    `target_id` bigint(20) UNSIGNED NOT NULL,
    `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
    `admin_id` bigint(20) UNSIGNED NOT NULL,
    `admin_level` enum(
        'super_admin',
        'admin',
        'support'
    ) NOT NULL DEFAULT 'admin',
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buyer_commissions`
--

CREATE TABLE `buyer_commissions` (
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `rate_percent` decimal(5, 2) NOT NULL,
    `amount` decimal(12, 2) NOT NULL,
    `collected_at` datetime DEFAULT NULL,
    `receipt_reference` varchar(100) DEFAULT NULL,
    `collected_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `buyer_commissions`
--

INSERT INTO
    `buyer_commissions` (
        `order_id`,
        `rate_percent`,
        `amount`,
        `collected_at`,
        `receipt_reference`,
        `collected_by`
    )
VALUES (
        1,
        5.00,
        125.00,
        NULL,
        NULL,
        NULL
    ),
    (
        9,
        5.00,
        250.00,
        NULL,
        NULL,
        NULL
    ),
    (
        10,
        5.00,
        250.00,
        NULL,
        NULL,
        NULL
    ),
    (
        11,
        5.00,
        1000.00,
        '2026-11-24 17:24:01',
        'R',
        3
    );

-- --------------------------------------------------------

--
-- Table structure for table `buyer_profiles`
--

CREATE TABLE `buyer_profiles` (
    `buyer_id` bigint(20) UNSIGNED NOT NULL,
    `business_name` varchar(150) NOT NULL,
    `business_type` varchar(100) DEFAULT NULL,
    `gst_number` varchar(20) DEFAULT NULL,
    `business_address` varchar(255) DEFAULT NULL,
    `delivery_address` varchar(255) DEFAULT NULL,
    `village_city` varchar(100) DEFAULT NULL,
    `district` varchar(100) DEFAULT NULL,
    `state` varchar(100) DEFAULT NULL,
    `pincode` varchar(10) DEFAULT NULL,
    `profile_image` varchar(255) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
    `dob` date DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `buyer_profiles`
--

INSERT INTO
    `buyer_profiles` (
        `buyer_id`,
        `business_name`,
        `business_type`,
        `gst_number`,
        `business_address`,
        `delivery_address`,
        `village_city`,
        `district`,
        `state`,
        `pincode`,
        `profile_image`,
        `created_at`,
        `updated_at`,
        `dob`
    )
VALUES (
        2,
        'Buyer Trades',
        'individual',
        NULL,
        'Punyapuram, Wyra',
        'Punyapuram, Wyra',
        'Punyapuram',
        'Khammam',
        'Telangana',
        '500456',
        NULL,
        '2026-11-22 19:11:45',
        '2026-11-22 19:11:45',
        NULL
    ),
    (
        5,
        'Sagar trades',
        'wholesaler',
        NULL,
        '209 - 231, Chirala',
        '209 - 231, Chirala',
        'Ekonda',
        'Chirala',
        'Telangana',
        '508025',
        NULL,
        '2026-11-24 09:56:43',
        '2026-11-24 09:56:43',
        NULL
    );

-- --------------------------------------------------------

--
-- Table structure for table `checkout_requests`
--

CREATE TABLE `checkout_requests` (
    `buyer_id` bigint(20) UNSIGNED NOT NULL,
    `request_key` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `listing_id` bigint(20) UNSIGNED NOT NULL,
    `order_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_bank_credits`
--

CREATE TABLE `commission_bank_credits` (
    `bank_reference` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `payer` enum('farmer', 'buyer') NOT NULL,
    `credited_amount` decimal(14, 2) NOT NULL,
    `recorded_by` bigint(20) UNSIGNED NOT NULL,
    `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
    `complaint_id` bigint(20) UNSIGNED NOT NULL,
    `complaint_number` varchar(30) NOT NULL,
    `raised_by` bigint(20) UNSIGNED NOT NULL,
    `order_id` bigint(20) UNSIGNED DEFAULT NULL,
    `subject` varchar(150) NOT NULL,
    `description` text NOT NULL,
    `priority` enum('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    `complaint_status` enum(
        'open',
        'in_progress',
        'resolved',
        'closed'
    ) NOT NULL DEFAULT 'open',
    `assigned_admin` bigint(20) UNSIGNED DEFAULT NULL,
    `resolution_note` text DEFAULT NULL,
    `resolved_at` datetime DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
    `message_id` bigint(20) UNSIGNED NOT NULL,
    `name` varchar(100) NOT NULL,
    `email` varchar(150) NOT NULL,
    `mobile` varchar(15) DEFAULT NULL,
    `subject` varchar(150) NOT NULL,
    `message` text NOT NULL,
    `message_status` enum(
        'new',
        'in_progress',
        'resolved'
    ) NOT NULL DEFAULT 'new',
    `assigned_admin` bigint(20) UNSIGNED DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crop_categories`
--

CREATE TABLE `crop_categories` (
    `category_id` int(10) UNSIGNED NOT NULL,
    `category_name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `crop_categories`
--

INSERT INTO
    `crop_categories` (
        `category_id`,
        `category_name`,
        `description`,
        `is_active`,
        `created_at`
    )
VALUES (
        1,
        'Cereals & Millets',
        'Rice, wheat, maize, jowar, and millets',
        1,
        '2026-11-22 15:26:23'
    ),
    (
        2,
        'Pulses',
        'Tur, chickpea, green gram, and other pulses',
        1,
        '2026-11-22 15:26:23'
    ),
    (
        3,
        'Vegetables',
        'Tomato, onion, potato, leafy vegetables, and others',
        1,
        '2026-11-22 15:26:23'
    ),
    (
        4,
        'Fruits',
        'Mango, banana, citrus fruits, and others',
        1,
        '2026-11-22 15:26:23'
    ),
    (
        5,
        'Spices',
        'Chilli, turmeric, ginger, and other spices',
        1,
        '2026-11-22 15:26:23'
    ),
    (
        6,
        'Oilseeds',
        'Groundnut, sunflower, sesame, and other oilseeds',
        1,
        '2026-11-22 15:26:23'
    ),
    (
        7,
        'Cash Crops',
        'Cotton, sugarcane, and other commercial crops',
        1,
        '2026-11-22 15:26:23'
    );

-- --------------------------------------------------------

--
-- Table structure for table `crop_listings`
--

CREATE TABLE `crop_listings` (
    `listing_id` bigint(20) UNSIGNED NOT NULL,
    `farmer_id` bigint(20) UNSIGNED NOT NULL,
    `category_id` int(10) UNSIGNED NOT NULL,
    `crop_name` varchar(120) NOT NULL,
    `variety` varchar(120) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `quantity_available` decimal(12, 3) NOT NULL,
    `quantity_unit` varchar(20) NOT NULL,
    `price_per_unit` decimal(12, 2) NOT NULL,
    `minimum_order_quantity` decimal(12, 3) DEFAULT NULL,
    `quality_grade` varchar(50) DEFAULT NULL,
    `harvest_date` date DEFAULT NULL,
    `available_from` date DEFAULT NULL,
    `available_until` date DEFAULT NULL,
    `listing_status` enum(
        'available',
        'paused',
        'sold_out',
        'removed'
    ) NOT NULL DEFAULT 'available',
    `created_at` datetime DEFAULT current_timestamp(),
    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `crop_listings`
--

INSERT INTO
    `crop_listings` (
        `listing_id`,
        `farmer_id`,
        `category_id`,
        `crop_name`,
        `variety`,
        `description`,
        `quantity_available`,
        `quantity_unit`,
        `price_per_unit`,
        `minimum_order_quantity`,
        `quality_grade`,
        `harvest_date`,
        `available_from`,
        `available_until`,
        `listing_status`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        1,
        1,
        'Rice',
        'Sunna Biyyam',
        'Best to cook',
        450.000,
        'kg',
        50.00,
        50.000,
        'grade_a',
        '2026-07-16',
        '2026-11-22',
        '2026-12-31',
        'available',
        '2026-11-22 19:20:12',
        '2026-11-22 19:25:33'
    ),
    (
        2,
        1,
        4,
        'Mango',
        'Rasalu',
        'Juicy Mangos',
        800.000,
        'kg',
        100.00,
        50.000,
        'grade_a',
        '2026-08-22',
        '2026-11-15',
        '2027-01-01',
        'available',
        '2026-11-22 21:28:25',
        '2026-11-24 17:20:13'
    ),
    (
        3,
        1,
        3,
        'Onion',
        '',
        'Fresh Onion Direct from the farm',
        400.000,
        'kg',
        50.00,
        50.000,
        'grade_a',
        '2026-05-06',
        '2026-11-23',
        '2026-12-31',
        'available',
        '2026-11-23 20:31:00',
        '2026-11-23 20:49:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `crop_payment_receipts`
--

CREATE TABLE `crop_payment_receipts` (
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `farmer_id` bigint(20) UNSIGNED NOT NULL,
    `confirmed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `crop_payment_receipts`
--

INSERT INTO
    `crop_payment_receipts` (
        `order_id`,
        `farmer_id`,
        `confirmed_at`
    )
VALUES (11, 1, '2026-11-24 17:57:41');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_profiles`
--

CREATE TABLE `farmer_profiles` (
    `farmer_id` bigint(20) UNSIGNED NOT NULL,
    `farm_name` varchar(150) DEFAULT NULL,
    `farm_description` text DEFAULT NULL,
    `address_line` varchar(255) NOT NULL,
    `village` varchar(100) NOT NULL,
    `district` varchar(100) NOT NULL,
    `state` varchar(100) NOT NULL,
    `pincode` varchar(10) NOT NULL,
    `profile_image` varchar(255) DEFAULT NULL,
    `verification_status` enum(
        'pending',
        'verified',
        'rejected'
    ) NOT NULL DEFAULT 'pending',
    `verified_by` bigint(20) DEFAULT NULL,
    `verified_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
    `land_size` decimal(10, 2) DEFAULT NULL,
    `dob` date DEFAULT NULL,
    `gender` enum('Male', 'Female', 'Other') DEFAULT NULL,
    `primary_crops` text DEFAULT NULL,
    `experience` int(10) UNSIGNED DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `farmer_profiles`
--

INSERT INTO
    `farmer_profiles` (
        `farmer_id`,
        `farm_name`,
        `farm_description`,
        `address_line`,
        `village`,
        `district`,
        `state`,
        `pincode`,
        `profile_image`,
        `verification_status`,
        `verified_by`,
        `verified_at`,
        `created_at`,
        `updated_at`,
        `land_size`,
        `dob`,
        `gender`,
        `primary_crops`,
        `experience`
    )
VALUES (
        1,
        'Rice',
        NULL,
        '13 -43',
        'Punyapuram',
        'Khammam',
        'Telangana',
        '503245',
        NULL,
        'pending',
        NULL,
        NULL,
        '2026-11-22 15:20:21',
        '2026-11-22 15:20:21',
        5.00,
        '1980-07-17',
        'Male',
        'Rice',
        30
    ),
    (
        3,
        NULL,
        NULL,
        'Indira Gandhi nagar, opp IDPL colony, near Dmart, Hyderabad',
        'Hyderabad',
        'Medchal-Malkajigiri',
        'Telangana',
        '500037',
        NULL,
        'pending',
        NULL,
        NULL,
        '2026-11-22 19:27:36',
        '2026-11-22 19:27:36',
        NULL,
        NULL,
        NULL,
        NULL,
        NULL
    ),
    (
        4,
        'Cotton',
        NULL,
        '20-23',
        'Ongole',
        'Prakasham',
        'Andhra Pradesh',
        '507845',
        NULL,
        'pending',
        NULL,
        NULL,
        '2026-11-24 09:52:46',
        '2026-11-24 09:52:46',
        10.00,
        NULL,
        NULL,
        NULL,
        NULL
    );

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

CREATE TABLE `listing_images` (
    `image_id` bigint(20) UNSIGNED NOT NULL,
    `listing_id` bigint(20) UNSIGNED NOT NULL,
    `image_path` varchar(255) NOT NULL,
    `alt_text` varchar(150) DEFAULT NULL,
    `is_primary` tinyint(1) NOT NULL DEFAULT 0,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `listing_images`
--

INSERT INTO
    `listing_images` (
        `image_id`,
        `listing_id`,
        `image_path`,
        `alt_text`,
        `is_primary`,
        `sort_order`,
        `created_at`
    )
VALUES (
        1,
        1,
        'uploads/crop_images/07fb833a860e4c5f8ec79eb0d1c1f1f2.jpg',
        'Rice image',
        1,
        1,
        '2026-11-22 19:20:12'
    ),
    (
        2,
        2,
        'uploads/crop_images/4a2b83140dc557fafccc45b28de36527.jpg',
        'Mango image',
        1,
        1,
        '2026-11-22 21:28:25'
    ),
    (
        3,
        3,
        'uploads/crop_images/710a7276e971f756d4ebdf17f95a11f0.jpg',
        'Onion image',
        1,
        1,
        '2026-11-23 20:31:00'
    );

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
    `notification_id` bigint(20) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `title` varchar(150) NOT NULL,
    `message` text NOT NULL,
    `notification_type` varchar(50) NOT NULL,
    `related_order_id` bigint(20) UNSIGNED DEFAULT NULL,
    `is_read` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` datetime DEFAULT current_timestamp(),
    `bell_seen` tinyint(1) NOT NULL DEFAULT 0
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO
    `notifications` (
        `notification_id`,
        `user_id`,
        `title`,
        `message`,
        `notification_type`,
        `related_order_id`,
        `is_read`,
        `created_at`,
        `bell_seen`
    )
VALUES (
        1,
        1,
        'New Order Received',
        'You have received a new order for Rice. Order number: AMC-20261122-C772FD7F',
        'new_order',
        1,
        1,
        '2026-11-22 19:25:02',
        1
    ),
    (
        2,
        2,
        'Order Accepted',
        'Your order AMC-20261122-C772FD7F for Rice has been accepted by the farmer.',
        'order_update',
        1,
        1,
        '2026-11-22 19:25:33',
        1
    ),
    (
        10,
        1,
        'New Order Received',
        'You have received a new order for Onion. Order number: AMC-20261123-17FF6413',
        'new_order',
        9,
        1,
        '2026-11-23 20:46:59',
        1
    ),
    (
        11,
        1,
        'New Order Received',
        'You have received a new order for Onion. Order number: AMC-20261123-231F6876',
        'new_order',
        10,
        1,
        '2026-11-23 20:47:32',
        1
    ),
    (
        12,
        2,
        'Order Accepted',
        'Your order AMC-20261123-231F6876 for Onion has been accepted by the farmer.',
        'order_update',
        10,
        1,
        '2026-11-23 20:49:43',
        1
    ),
    (
        13,
        1,
        'New Order Received',
        'You have received a new order for Mango. Order number: AMC-20261123-4044265E',
        'new_order',
        11,
        1,
        '2026-11-23 20:53:17',
        1
    ),
    (
        14,
        2,
        'Order Rejected',
        'Your order AMC-20261123-17FF6413 for Onion has been rejected by the farmer.',
        'order_update',
        9,
        1,
        '2026-11-23 20:54:28',
        1
    ),
    (
        15,
        2,
        'Order Accepted',
        'Your order AMC-20261123-4044265E for Mango has been accepted by the farmer.',
        'order_update',
        11,
        1,
        '2026-11-24 17:20:13',
        1
    ),
    (
        16,
        2,
        'Order ready for pickup',
        'AMC-20261123-4044265E — The farmer marked the order ready for pickup. Pickup location: 13 -43',
        'order_update',
        11,
        1,
        '2026-11-24 17:21:25',
        1
    ),
    (
        17,
        1,
        'Order completed',
        'AMC-20261123-4044265E — The buyer confirmed receipt of the crop.',
        'order_update',
        11,
        1,
        '2026-11-24 17:22:53',
        1
    );

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `order_number` varchar(30) NOT NULL,
    `buyer_id` bigint(20) UNSIGNED NOT NULL,
    `farmer_id` bigint(20) UNSIGNED NOT NULL,
    `listing_id` bigint(20) UNSIGNED NOT NULL,
    `quantity` decimal(12, 3) NOT NULL,
    `quantity_unit` varchar(20) NOT NULL,
    `unit_price` decimal(12, 2) NOT NULL,
    `subtotal` decimal(12, 2) NOT NULL,
    `delivery_address` varchar(255) NOT NULL,
    `delivery_city` varchar(100) NOT NULL,
    `delivery_state` varchar(100) NOT NULL,
    `delivery_pincode` varchar(10) NOT NULL,
    `buyer_note` text DEFAULT NULL,
    `farmer_response_note` text DEFAULT NULL,
    `order_status` enum(
        'pending',
        'accepted',
        'rejected',
        'cancelled',
        'completed',
        'dispatched',
        'ready_for_pickup'
    ) NOT NULL DEFAULT 'pending',
    `terms_accepted_at` datetime DEFAULT NULL,
    `terms_version` varchar(30) DEFAULT NULL,
    `placed_at` datetime DEFAULT current_timestamp(),
    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `fulfilment_method` enum('delivery', 'pickup') NOT NULL DEFAULT 'delivery'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO
    `orders` (
        `order_id`,
        `order_number`,
        `buyer_id`,
        `farmer_id`,
        `listing_id`,
        `quantity`,
        `quantity_unit`,
        `unit_price`,
        `subtotal`,
        `delivery_address`,
        `delivery_city`,
        `delivery_state`,
        `delivery_pincode`,
        `buyer_note`,
        `farmer_response_note`,
        `order_status`,
        `terms_accepted_at`,
        `terms_version`,
        `placed_at`,
        `updated_at`,
        `fulfilment_method`
    )
VALUES (
        1,
        'AMC-20261122-C772FD7F',
        2,
        1,
        1,
        50.000,
        'kg',
        50.00,
        2500.00,
        'Indira Gandhi nagar, opp IDPL colony, near Dmart, Hyderabad',
        'Hyderabad',
        'Telangana',
        '500037',
        'Something',
        'Farmer accepted the order after agreeing to the displayed terms.',
        'accepted',
        '2026-11-22 19:25:33',
        '1.0',
        '2026-11-22 19:25:02',
        '2026-11-22 19:25:33',
        'delivery'
    ),
    (
        9,
        'AMC-20261123-17FF6413',
        2,
        1,
        3,
        100.000,
        'kg',
        50.00,
        5000.00,
        '13-73, Indra Gandhi nagar, Opp IDPL colony, near Dmart, Quthubullapur, Medchal-malkajigiri',
        'Hyderbad',
        'Telagana',
        '500037',
        '',
        'Farmer rejected the order.',
        'rejected',
        NULL,
        NULL,
        '2026-11-23 20:46:59',
        '2026-11-23 20:54:28',
        'delivery'
    ),
    (
        10,
        'AMC-20261123-231F6876',
        2,
        1,
        3,
        100.000,
        'kg',
        50.00,
        5000.00,
        '13-73, Indra Gandhi nagar, Opp IDPL colony, near Dmart, Quthubullapur, Medchal-malkajigiri',
        'Hyderbad',
        'Telagana',
        '500037',
        'Something',
        'Farmer accepted the order after agreeing to the displayed terms.',
        'accepted',
        '2026-11-23 20:49:43',
        '1.0',
        '2026-11-23 20:47:32',
        '2026-11-23 20:49:43',
        'delivery'
    ),
    (
        11,
        'AMC-20261123-4044265E',
        2,
        1,
        2,
        200.000,
        'kg',
        100.00,
        20000.00,
        '',
        '',
        '',
        '',
        'Something',
        'Farmer accepted the order after agreeing to the displayed terms.',
        'completed',
        '2026-11-24 17:20:13',
        '1.0',
        '2026-11-23 20:53:17',
        '2026-11-24 17:22:53',
        'pickup'
    );

-- --------------------------------------------------------

--
-- Table structure for table `order_commissions`
--

CREATE TABLE `order_commissions` (
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `rate_percent` decimal(5, 2) NOT NULL,
    `amount` decimal(12, 2) NOT NULL,
    `collected_at` datetime DEFAULT NULL,
    `receipt_reference` varchar(100) DEFAULT NULL,
    `collected_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `order_commissions`
--

INSERT INTO
    `order_commissions` (
        `order_id`,
        `rate_percent`,
        `amount`,
        `collected_at`,
        `receipt_reference`,
        `collected_by`
    )
VALUES (
        1,
        2.00,
        50.00,
        NULL,
        NULL,
        NULL
    ),
    (
        9,
        2.00,
        100.00,
        NULL,
        NULL,
        NULL
    ),
    (
        10,
        2.00,
        100.00,
        NULL,
        NULL,
        NULL
    ),
    (
        11,
        2.00,
        400.00,
        NULL,
        NULL,
        NULL
    );

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
    `history_id` bigint(20) UNSIGNED NOT NULL,
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
    `old_status` varchar(30) DEFAULT NULL,
    `new_status` varchar(30) NOT NULL,
    `remarks` text DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO
    `order_status_history` (
        `history_id`,
        `order_id`,
        `changed_by`,
        `old_status`,
        `new_status`,
        `remarks`,
        `created_at`
    )
VALUES (
        1,
        1,
        2,
        NULL,
        'pending',
        'Order placed by buyer.',
        '2026-11-22 19:25:02'
    ),
    (
        2,
        1,
        1,
        'pending',
        'accepted',
        'Farmer accepted the order.',
        '2026-11-22 19:25:33'
    ),
    (
        10,
        9,
        2,
        NULL,
        'pending',
        'Order placed by buyer.',
        '2026-11-23 20:46:59'
    ),
    (
        11,
        10,
        2,
        NULL,
        'pending',
        'Order placed by buyer.',
        '2026-11-23 20:47:32'
    ),
    (
        12,
        10,
        1,
        'pending',
        'accepted',
        'Farmer accepted the order.',
        '2026-11-23 20:49:43'
    ),
    (
        13,
        11,
        2,
        NULL,
        'pending',
        'Order placed by buyer.',
        '2026-11-23 20:53:17'
    ),
    (
        14,
        9,
        1,
        'pending',
        'rejected',
        'Farmer rejected the order.',
        '2026-11-23 20:54:28'
    ),
    (
        15,
        11,
        1,
        'pending',
        'accepted',
        'Farmer accepted the order.',
        '2026-11-24 17:20:13'
    ),
    (
        16,
        11,
        1,
        'accepted',
        'ready_for_pickup',
        'The farmer marked the order ready for pickup. Pickup location: 13 -43',
        '2026-11-24 17:21:25'
    ),
    (
        17,
        11,
        2,
        'ready_for_pickup',
        'completed',
        'The buyer confirmed receipt of the crop.',
        '2026-11-24 17:22:53'
    );

-- --------------------------------------------------------

--
-- Table structure for table `platform_reviews`
--

CREATE TABLE `platform_reviews` (
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `rating` tinyint(3) UNSIGNED NOT NULL,
    `review_text` varchar(400) NOT NULL,
    `updated_at` datetime NOT NULL DEFAULT current_timestamp()
);

--
-- Dumping data for table `platform_reviews`
--

INSERT INTO
    `platform_reviews` (
        `user_id`,
        `rating`,
        `review_text`,
        `updated_at`
    )
VALUES (
        1,
        5,
        'Very Good Platform',
        '2026-11-23 17:15:59'
    );

-- --------------------------------------------------------

--
-- Table structure for table `support_requests`
--

CREATE TABLE `support_requests` (
    `request_id` int(10) UNSIGNED NOT NULL,
    `ticket_number` varchar(32) NOT NULL,
    `user_id` bigint(20) UNSIGNED DEFAULT NULL,
    `contact_name` varchar(120) NOT NULL,
    `email` varchar(254) NOT NULL,
    `mobile` varchar(24) NOT NULL DEFAULT '',
    `contact_role` varchar(12) NOT NULL,
    `subject` varchar(100) NOT NULL,
    `reference_text` varchar(100) NOT NULL DEFAULT '',
    `description` text NOT NULL,
    `status` enum(
        'open',
        'in_progress',
        'resolved',
        'closed'
    ) NOT NULL DEFAULT 'open',
    `reply` text DEFAULT NULL,
    `revision` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `file_type` varchar(40) DEFAULT NULL,
    `file_data` mediumblob DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactional_emails`
--

CREATE TABLE `transactional_emails` (
    `message_key` varchar(100) NOT NULL,
    `recipient` varchar(254) NOT NULL,
    `subject_line` varchar(200) NOT NULL,
    `body` text NOT NULL,
    `retry_at` datetime NOT NULL DEFAULT current_timestamp(),
    `sent_at` datetime DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `transactional_emails`
--

INSERT INTO
    `transactional_emails` (
        `message_key`,
        `recipient`,
        `subject_line`,
        `body`,
        `retry_at`,
        `sent_at`
    )
VALUES (
        'order-10-placed-buyer',
        'sanjaychukka7@gmail.com',
        'AgroMarket order placed - AMC-20261123-231F6876',
        'Hello Buyer 1,\n\nOrder: AMC-20261123-231F6876\nPlaced: 2026-11-23 20:47:32\nStatus: pending\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Onion\nQuantity: 100.000 kg\nUnit price: INR 50.00\nCrop subtotal: INR 5000.00\nFulfilment: delivery\nDelivery address: 13-73, Indra Gandhi nagar, Opp IDPL colony, near Dmart, Quthubullapur, Medchal-malkajigiri, Hyderbad, Telagana 500037\nBuyer fee: INR 250.00\nTotal including fee: INR 5,250.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-23 20:52:32',
        '2026-11-23 20:47:38'
    ),
    (
        'order-10-placed-farmer',
        'sanjaychukka1312@gmail.com',
        'AgroMarket order placed - AMC-20261123-231F6876',
        'Hello Farmer 1,\n\nOrder: AMC-20261123-231F6876\nPlaced: 2026-11-23 20:47:32\nStatus: pending\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Onion\nQuantity: 100.000 kg\nUnit price: INR 50.00\nCrop subtotal: INR 5000.00\nFulfilment: delivery\nDelivery address: 13-73, Indra Gandhi nagar, Opp IDPL colony, near Dmart, Quthubullapur, Medchal-malkajigiri, Hyderbad, Telagana 500037\nFarmer fee: INR 100.00\nProceeds after fee: INR 4,900.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-23 20:52:38',
        '2026-11-23 20:47:43'
    ),
    (
        'order-11-completed-buyer',
        'sanjaychukka7@gmail.com',
        'AgroMarket order completed - AMC-20261123-4044265E',
        'Hello Buyer 1,\n\nOrder: AMC-20261123-4044265E\nPlaced: 2026-11-23 20:53:17\nStatus: completed\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Mango\nQuantity: 200.000 kg\nUnit price: INR 100.00\nCrop subtotal: INR 20000.00\nFulfilment: pickup\nBuyer fee: INR 1000.00\nTotal including fee: INR 21,000.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-24 17:27:53',
        '2026-11-24 17:22:58'
    ),
    (
        'order-11-completed-farmer',
        'sanjaychukka1312@gmail.com',
        'AgroMarket order completed - AMC-20261123-4044265E',
        'Hello Farmer 1,\n\nOrder: AMC-20261123-4044265E\nPlaced: 2026-11-23 20:53:17\nStatus: completed\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Mango\nQuantity: 200.000 kg\nUnit price: INR 100.00\nCrop subtotal: INR 20000.00\nFulfilment: pickup\nFarmer fee: INR 400.00\nProceeds after fee: INR 19,600.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-24 17:27:58',
        '2026-11-24 17:23:03'
    ),
    (
        'order-11-placed-buyer',
        'sanjaychukka7@gmail.com',
        'AgroMarket order placed - AMC-20261123-4044265E',
        'Hello Buyer 1,\n\nOrder: AMC-20261123-4044265E\nPlaced: 2026-11-23 20:53:17\nStatus: pending\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Mango\nQuantity: 200.000 kg\nUnit price: INR 100.00\nCrop subtotal: INR 20000.00\nFulfilment: pickup\nBuyer fee: INR 1000.00\nTotal including fee: INR 21,000.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-23 20:58:17',
        '2026-11-23 20:53:24'
    ),
    (
        'order-11-placed-farmer',
        'sanjaychukka1312@gmail.com',
        'AgroMarket order placed - AMC-20261123-4044265E',
        'Hello Farmer 1,\n\nOrder: AMC-20261123-4044265E\nPlaced: 2026-11-23 20:53:17\nStatus: pending\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Mango\nQuantity: 200.000 kg\nUnit price: INR 100.00\nCrop subtotal: INR 20000.00\nFulfilment: pickup\nFarmer fee: INR 400.00\nProceeds after fee: INR 19,600.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-23 20:58:24',
        '2026-11-23 20:53:29'
    ),
    (
        'order-9-placed-buyer',
        'sanjaychukka7@gmail.com',
        'AgroMarket order placed - AMC-20261123-17FF6413',
        'Hello Buyer 1,\n\nOrder: AMC-20261123-17FF6413\nPlaced: 2026-11-23 20:46:59\nStatus: pending\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Onion\nQuantity: 100.000 kg\nUnit price: INR 50.00\nCrop subtotal: INR 5000.00\nFulfilment: delivery\nDelivery address: 13-73, Indra Gandhi nagar, Opp IDPL colony, near Dmart, Quthubullapur, Medchal-malkajigiri, Hyderbad, Telagana 500037\nBuyer fee: INR 250.00\nTotal including fee: INR 5,250.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-23 20:51:59',
        '2026-11-23 20:47:06'
    ),
    (
        'order-9-placed-farmer',
        'sanjaychukka1312@gmail.com',
        'AgroMarket order placed - AMC-20261123-17FF6413',
        'Hello Farmer 1,\n\nOrder: AMC-20261123-17FF6413\nPlaced: 2026-11-23 20:46:59\nStatus: pending\nBuyer: Buyer 1\nFarmer: Farmer 1\nCrop: Onion\nQuantity: 100.000 kg\nUnit price: INR 50.00\nCrop subtotal: INR 5000.00\nFulfilment: delivery\nDelivery address: 13-73, Indra Gandhi nagar, Opp IDPL colony, near Dmart, Quthubullapur, Medchal-malkajigiri, Hyderbad, Telagana 500037\nFarmer fee: INR 100.00\nProceeds after fee: INR 4,900.00\n\nView this order in your AgroMarket dashboard.',
        '2026-11-23 20:52:06',
        '2026-11-23 20:47:11'
    ),
    (
        'welcome-4',
        'mohansai4703@gmail.com',
        'Welcome to AgroMarket Connect',
        'Hello Farmer 2,\n\nYour farmer account is active.\nUser ID: 4\nUsername: farmer_2\nVerified email: mohansai4703@gmail.com\nYou can now sign in.',
        '2026-11-24 09:57:46',
        '2026-11-24 09:52:51'
    ),
    (
        'welcome-5',
        'darlingsagar717@gmail.com',
        'Welcome to AgroMarket Connect',
        'Hello Buyer 2,\n\nYour buyer account is active.\nUser ID: 5\nUsername: buyer_2\nVerified email: darlingsagar717@gmail.com\nYou can now sign in.',
        '2026-11-24 10:01:43',
        '2026-11-24 09:56:48'
    );

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `role` enum('admin', 'farmer', 'buyer') NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `email` varchar(150) DEFAULT NULL,
    `mobile` varchar(15) DEFAULT NULL,
    `password_hash` varchar(255) NOT NULL,
    `account_status` enum(
        'pending',
        'active',
        'suspended',
        'closed'
    ) NOT NULL DEFAULT 'pending',
    `email_verified_at` datetime DEFAULT NULL,
    `mobile_verified_at` datetime DEFAULT NULL,
    `last_login_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
    `token` varchar(64) DEFAULT NULL,
    `username` varchar(50) NOT NULL,
    `last_login` datetime DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO
    `users` (
        `user_id`,
        `role`,
        `full_name`,
        `email`,
        `mobile`,
        `password_hash`,
        `account_status`,
        `email_verified_at`,
        `mobile_verified_at`,
        `last_login_at`,
        `created_at`,
        `updated_at`,
        `token`,
        `username`,
        `last_login`
    )
VALUES (
        1,
        'farmer',
        'Farmer 1',
        'sanjaychukka1312@gmail.com',
        '7032412044',
        '$2y$10$lkc.BMbHGhmaGn37yJh/weV7MqsHn8A78J6oOhsb1tVmwpF30nyCG',
        'active',
        '2026-11-22 15:21:26',
        NULL,
        NULL,
        '2026-11-22 15:20:21',
        '2026-11-22 15:20:21',
        NULL,
        'farmer_1',
        '2026-11-25 10:20:15'
    ),
    (
        2,
        'buyer',
        'Buyer 1',
        'sanjaychukka7@gmail.com',
        '7032412045',
        '$2y$10$gHhmoP7bWR2r6ch7QYgQ7uqWWEtJcZKv7rCCISXN4Gxwuqwpsccde',
        'active',
        '2026-11-22 19:12:10',
        NULL,
        NULL,
        '2026-11-22 19:11:45',
        '2026-11-22 19:11:45',
        NULL,
        'buyer_1',
        '2026-11-25 10:46:42'
    ),
    (
        3,
        'admin',
        'Admin 1',
        'sanjaychukka14@gmail.com',
        '7032412046',
        '$2y$10$cnDQPO7BfCkjIhZQqCquvu99XLR3baO7Ae7MB0mlgXTeNpxo3eh7O',
        'active',
        '2026-11-22 19:27:59',
        NULL,
        NULL,
        '2026-11-22 19:27:36',
        '2026-11-22 19:27:36',
        NULL,
        'admin_1',
        '2026-11-25 10:06:22'
    ),
    (
        4,
        'farmer',
        'Farmer 2',
        'mohansai4703@gmail.com',
        '7032412465',
        '$2y$10$Rbk5FxEWeypR1LjZSJwP7etH5IHDkVoq09qxmhFMeWlUVWUT/j3My',
        'active',
        '2026-11-24 09:52:46',
        NULL,
        NULL,
        '2026-11-24 09:52:46',
        '2026-11-24 09:52:46',
        NULL,
        'farmer_2',
        '2026-11-24 09:53:13'
    ),
    (
        5,
        'buyer',
        'Buyer 2',
        'darlingsagar717@gmail.com',
        '7032418457',
        '$2y$10$pM6VVzVBf.UIXuAUe.GwV.pb20mriu72ADc1wlzRAxVdh9K74XSzW',
        'active',
        '2026-11-24 09:56:43',
        NULL,
        NULL,
        '2026-11-24 09:56:43',
        '2026-11-24 09:56:43',
        NULL,
        'buyer_2',
        '2026-11-24 09:57:09'
    );

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
    `verification_id` bigint(20) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED DEFAULT NULL,
    `identifier` varchar(150) NOT NULL,
    `channel` enum('email', 'sms') NOT NULL,
    `purpose` enum(
        'registration',
        'password_reset'
    ) NOT NULL,
    `code_hash` varchar(255) NOT NULL,
    `expires_at` datetime NOT NULL,
    `verified_at` datetime DEFAULT NULL,
    `attempts` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO
    `verification_codes` (
        `verification_id`,
        `user_id`,
        `identifier`,
        `channel`,
        `purpose`,
        `code_hash`,
        `expires_at`,
        `verified_at`,
        `attempts`,
        `created_at`
    )
VALUES (
        1,
        2,
        'sanjaychukka7@gmail.com',
        'email',
        'password_reset',
        '',
        '2026-11-23 20:58:57',
        '2026-11-23 20:58:04',
        1,
        '2026-11-23 20:57:38'
    ),
    (
        2,
        NULL,
        'mohansai4703@gmail.com',
        'email',
        'registration',
        '',
        '2026-11-24 09:52:46',
        '2026-11-24 09:51:36',
        1,
        '2026-11-24 09:51:08'
    ),
    (
        3,
        NULL,
        'darlingsagar717@gmail.com',
        'email',
        'registration',
        '',
        '2026-11-24 09:56:43',
        '2026-11-24 09:55:28',
        1,
        '2026-11-24 09:54:59'
    ),
    (
        4,
        NULL,
        'test@gmail.com',
        'email',
        'registration',
        'f78d0d1282797521b505a4a6d9a0d3985ea83d200fefd8b825189b035f85fe68',
        '2026-11-24 15:59:51',
        NULL,
        2,
        '2026-11-24 15:49:51'
    );

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
ADD PRIMARY KEY (`log_id`),
ADD KEY `idx_audit_admin` (`admin_id`);

--
-- Indexes for table `admin_profiles`
--
ALTER TABLE `admin_profiles` ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `buyer_commissions`
--
ALTER TABLE `buyer_commissions`
ADD PRIMARY KEY (`order_id`),
ADD KEY `collected_by` (`collected_by`);

--
-- Indexes for table `buyer_profiles`
--
ALTER TABLE `buyer_profiles` ADD PRIMARY KEY (`buyer_id`);

--
-- Indexes for table `checkout_requests`
--
ALTER TABLE `checkout_requests`
ADD PRIMARY KEY (`buyer_id`, `request_key`),
ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `commission_bank_credits`
--
ALTER TABLE `commission_bank_credits`
ADD PRIMARY KEY (`bank_reference`),
ADD UNIQUE KEY `order_fee` (`order_id`, `payer`),
ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
ADD PRIMARY KEY (`complaint_id`),
ADD UNIQUE KEY `complaint_number` (`complaint_number`),
ADD KEY `idx_complaint_user` (`raised_by`),
ADD KEY `idx_complaint_order` (`order_id`),
ADD KEY `idx_complaint_admin` (`assigned_admin`),
ADD KEY `idx_complaint_status` (`complaint_status`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
ADD PRIMARY KEY (`message_id`),
ADD KEY `idx_contact_status` (`message_status`),
ADD KEY `idx_contact_admin` (`assigned_admin`);

--
-- Indexes for table `crop_categories`
--
ALTER TABLE `crop_categories`
ADD PRIMARY KEY (`category_id`),
ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `crop_listings`
--
ALTER TABLE `crop_listings`
ADD PRIMARY KEY (`listing_id`),
ADD KEY `idx_listing_farmer` (`farmer_id`),
ADD KEY `idx_listing_category` (`category_id`),
ADD KEY `idx_listing_status` (`listing_status`);

--
-- Indexes for table `crop_payment_receipts`
--
ALTER TABLE `crop_payment_receipts`
ADD PRIMARY KEY (`order_id`),
ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
ADD PRIMARY KEY (`farmer_id`),
ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `listing_images`
--
ALTER TABLE `listing_images`
ADD PRIMARY KEY (`image_id`),
ADD KEY `idx_image_listing` (`listing_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
ADD PRIMARY KEY (`notification_id`),
ADD KEY `idx_notification_user` (`user_id`),
ADD KEY `idx_notification_order` (`related_order_id`),
ADD KEY `idx_notification_badge` (
    `user_id`,
    `bell_seen`,
    `is_read`
);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
ADD PRIMARY KEY (`order_id`),
ADD UNIQUE KEY `order_number` (`order_number`),
ADD KEY `idx_order_buyer` (`buyer_id`),
ADD KEY `idx_order_farmer` (`farmer_id`),
ADD KEY `idx_order_listing` (`listing_id`),
ADD KEY `idx_order_status` (`order_status`);

--
-- Indexes for table `order_commissions`
--
ALTER TABLE `order_commissions`
ADD PRIMARY KEY (`order_id`),
ADD KEY `collected_by` (`collected_by`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
ADD PRIMARY KEY (`history_id`),
ADD KEY `idx_history_order` (`order_id`),
ADD KEY `idx_history_user` (`changed_by`);

--
-- Indexes for table `platform_reviews`
--
ALTER TABLE `platform_reviews` ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `support_requests`
--
ALTER TABLE `support_requests`
ADD PRIMARY KEY (`request_id`),
ADD UNIQUE KEY `ticket_number` (`ticket_number`),
ADD KEY `user_id` (`user_id`, `created_at`),
ADD KEY `status` (`status`);

--
-- Indexes for table `transactional_emails`
--
ALTER TABLE `transactional_emails`
ADD PRIMARY KEY (`message_key`),
ADD KEY `sent_at` (`sent_at`, `retry_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
ADD PRIMARY KEY (`user_id`),
ADD UNIQUE KEY `username` (`username`),
ADD UNIQUE KEY `email` (`email`),
ADD UNIQUE KEY `token` (`token`),
ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
ADD PRIMARY KEY (`verification_id`),
ADD KEY `idx_verification_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
MODIFY `complaint_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
MODIFY `message_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crop_categories`
--
ALTER TABLE `crop_categories`
MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `crop_listings`
--
ALTER TABLE `crop_listings`
MODIFY `listing_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `listing_images`
--
ALTER TABLE `listing_images`
MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 12;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
MODIFY `history_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 18;

--
-- AUTO_INCREMENT for table `support_requests`
--
ALTER TABLE `support_requests`
MODIFY `request_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
MODIFY `verification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
ADD CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_profiles` (`admin_id`);

--
-- Constraints for table `buyer_commissions`
--
ALTER TABLE `buyer_commissions`
ADD CONSTRAINT `buyer_commissions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
ADD CONSTRAINT `buyer_commissions_ibfk_2` FOREIGN KEY (`collected_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `checkout_requests`
--
ALTER TABLE `checkout_requests`
ADD CONSTRAINT `checkout_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `commission_bank_credits`
--
ALTER TABLE `commission_bank_credits`
ADD CONSTRAINT `commission_bank_credits_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
ADD CONSTRAINT `commission_bank_credits_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
ADD CONSTRAINT `fk_complaint_admin` FOREIGN KEY (`assigned_admin`) REFERENCES `admin_profiles` (`admin_id`),
ADD CONSTRAINT `fk_complaint_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
ADD CONSTRAINT `fk_complaint_user` FOREIGN KEY (`raised_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
ADD CONSTRAINT `fk_contact_admin` FOREIGN KEY (`assigned_admin`) REFERENCES `admin_profiles` (`admin_id`);

--
-- Constraints for table `crop_listings`
--
ALTER TABLE `crop_listings`
ADD CONSTRAINT `fk_listing_category` FOREIGN KEY (`category_id`) REFERENCES `crop_categories` (`category_id`),
ADD CONSTRAINT `fk_listing_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`farmer_id`);

--
-- Constraints for table `crop_payment_receipts`
--
ALTER TABLE `crop_payment_receipts`
ADD CONSTRAINT `crop_payment_receipts_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
ADD CONSTRAINT `crop_payment_receipts_ibfk_2` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `listing_images`
--
ALTER TABLE `listing_images`
ADD CONSTRAINT `fk_image_listing` FOREIGN KEY (`listing_id`) REFERENCES `crop_listings` (`listing_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
ADD CONSTRAINT `fk_notification_order` FOREIGN KEY (`related_order_id`) REFERENCES `orders` (`order_id`),
ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
ADD CONSTRAINT `fk_order_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `buyer_profiles` (`buyer_id`),
ADD CONSTRAINT `fk_order_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`farmer_id`),
ADD CONSTRAINT `fk_order_listing` FOREIGN KEY (`listing_id`) REFERENCES `crop_listings` (`listing_id`);

--
-- Constraints for table `order_commissions`
--
ALTER TABLE `order_commissions`
ADD CONSTRAINT `order_commissions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
ADD CONSTRAINT `order_commissions_ibfk_2` FOREIGN KEY (`collected_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
ADD CONSTRAINT `fk_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `platform_reviews`
--
ALTER TABLE `platform_reviews`
ADD CONSTRAINT `platform_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `verification_codes`
--
ALTER TABLE `verification_codes`
ADD CONSTRAINT `fk_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;