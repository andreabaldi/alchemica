-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Feb 06, 2026 at 04:04 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Alcheneg`
--

-- --------------------------------------------------------

--
-- Table structure for table `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1769528793),
('m130524_201442_init', 1769528849),
('m190124_110200_add_verification_token_column_to_user_table', 1769528849),
('m260127_154548_create_presets_table', 1769528849),
('m260202_101746_rename_paper_to_support_in_presets', 1770204632),
('m260204_113407_add_custom_gamma_to_presets', 1770204941);

-- --------------------------------------------------------

--
-- Table structure for table `presets`
--

CREATE TABLE `presets` (
  `id` int NOT NULL,
  `technique_name` varchar(255) NOT NULL,
  `gamma_base` float DEFAULT NULL,
  `gamma_step` float DEFAULT NULL,
  `gamma_mode` varchar(20) NOT NULL DEFAULT 'step',
  `gamma_custom_list` text,
  `show_wedge_default` tinyint(1) DEFAULT '1',
  `color_hex` varchar(7) DEFAULT '#000000',
  `ink_limit` int DEFAULT '100',
  `paper_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `paper_id` int DEFAULT NULL,
  `uv_exposure_seconds` int DEFAULT NULL,
  `notes` text,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `presets`
--

INSERT INTO `presets` (`id`, `technique_name`, `gamma_base`, `gamma_step`, `gamma_mode`, `gamma_custom_list`, `show_wedge_default`, `color_hex`, `ink_limit`, `paper_name`, `paper_id`, `uv_exposure_seconds`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Cianotipia Classica', 1.8, 0.2, 'list', '1.0,1.4,2.2,3.0', 1, '#ff2600', 100, 'Pictorico', NULL, 1400, 'Tempo si carta Bergger cot 320', NULL, NULL),
(2, 'Ziatype', 2.2, 0.2, 'step', NULL, 1, '#000000', 100, 'Pictorico', NULL, 800, ' Tempo si carta Bergger cot 320', NULL, NULL),
(3, 'Platinotipia', 2.4, 0.15, 'step', NULL, 1, '#000000', 100, 'Pictorico', NULL, 600, 'Esposizione lunga, contrasto massimo.', NULL, NULL),
(4, 'Gomma Bicromatata', 1.6, 0.2, 'step', NULL, 1, '#000000', 95, 'Pictorico', NULL, 300, 'Step alto per gestire le stratificazioni.', NULL, NULL),
(5, 'Van Dyke Brown', 1.8, 0.25, 'list', '1.4,1.8,2.4', 1, '#000000', 100, 'Pictorico', NULL, 800, 'Ottima separazione nei mezzitoni.', NULL, NULL),
(6, 'Salt Print', 2.1, 0.15, 'step', NULL, 1, '#000000', 100, 'Pictorico', NULL, 900, 'Richiede carta salata e nitrato d\'argento.', NULL, NULL),
(7, 'Carbon Print', 2.8, 0.25, 'step', NULL, 1, '#000000', 100, 'Pictorico', NULL, 1200, 'Massimo contrasto e densità UV.', NULL, NULL),
(8, 'Cianotipia Mike Ware', 1.8, 0.2, 'step', NULL, 1, '#ff2600', 100, 'Pictorico', NULL, 750, 'Tempo su carta Bergger cot 320', NULL, NULL),
(9, 'Calibration ', 0.1, 0.3, 'step', NULL, 1, '#00f900', 100, 'Pictorico', NULL, 700, 'Profile di calibrazione', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` smallint NOT NULL DEFAULT '10',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL,
  `verification_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `created_at`, `updated_at`, `verification_token`) VALUES
(33, 'topastro', '6WOO7gOmsP44BhNNpGQmMan3ZmFjDqgO', '$2y$13$wtpygeHS.Qc77uERNo0Usu3v.hP5VTXKSgDUj2gL2uS7U1/4vNJbu', NULL, 'abaldi@tiscali.it', 10, 1742480897, 1742480897, 'b44lTKTTIL85M9dyMBeC8OSpSRB6TLtH_1742480897'),
(35, 'Andrea', 'xS8nbmGzIy3I8pJ2empIPbZmt_bxLUo7', '$2y$13$GZ26Lgaa00uTFTRsIKlhB.jI/zcUYUNSTq7P.ODx4R8T7r1GJNBy6', NULL, 'baldi.andrea@gmail.com', 10, 1768747134, 1768747145, 'VLnL10u_W4wqWMisujbeDZ6SnaX2jUPX_1768747134');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `presets`
--
ALTER TABLE `presets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `password_reset_token` (`password_reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `presets`
--
ALTER TABLE `presets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
