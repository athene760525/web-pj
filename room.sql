-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-11-20 20:26:03
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `room`
--

-- --------------------------------------------------------

--
-- 資料表結構 `household`
--

CREATE TABLE `household` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '住宿流水號（主鍵）',
  `StID` varchar(20) NOT NULL COMMENT '學號（FK → users.account）',
  `semester` char(9) NOT NULL COMMENT '學期，如 114-1',
  `name` varchar(50) NOT NULL COMMENT '姓名（從 users 快取）',
  `gender` char(1) NOT NULL COMMENT '性別',
  `number` int(11) NOT NULL COMMENT '房號',
  `stphone` varchar(20) DEFAULT NULL COMMENT '學生電話',
  `Contact` varchar(50) DEFAULT NULL COMMENT '緊急聯絡人',
  `relation` varchar(10) DEFAULT NULL COMMENT '關係',
  `rephone` varchar(20) DEFAULT NULL COMMENT '緊急電話',
  `check_in_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '入住時間',
  `check_out_at` datetime DEFAULT NULL COMMENT '退宿時間（NULL 表示尚未退宿）'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `household`
--

INSERT INTO `household` (`id`, `StID`, `semester`, `name`, `gender`, `number`, `stphone`, `Contact`, `relation`, `rephone`, `check_in_at`, `check_out_at`) VALUES
(1, '4125845', '114-1', '小雯', '女', 601, '0912000111', '王媽媽', '母女', '0922333444', '2025-11-21 03:16:00', NULL),
(2, '4110854', '114-1', '小婷', '女', 602, '0922333555', '李媽媽', '母女', '0933444555', '2025-11-21 03:16:00', NULL),
(3, '4102282', '114-1', '小宜', '女', 603, '0933222111', '張爸爸', '父女', '0944555666', '2025-11-21 03:16:00', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `sign_in`
--

CREATE TABLE `sign_in` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '簽到紀錄編號',
  `household_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → household.id',
  `StID` varchar(20) NOT NULL COMMENT '學號',
  `time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '簽到時間',
  `method` enum('管理員登記','住民回報') NOT NULL COMMENT '簽到方式'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `sign_in`
--

INSERT INTO `sign_in` (`id`, `household_id`, `StID`, `time`, `method`) VALUES
(1, 1, '4125845', '2025-11-21 03:23:14', '住民回報'),
(2, 2, '4110854', '2025-11-21 03:23:14', '管理員登記'),
(3, 3, '4102282', '2025-11-21 03:23:14', '住民回報');

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `account` varchar(20) NOT NULL COMMENT '帳號/學號',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `password` varchar(255) NOT NULL COMMENT '密碼（建議存雜湊值）',
  `identity` enum('管理員','住戶','舍監') NOT NULL COMMENT '身份'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`account`, `name`, `password`, `identity`) VALUES
('4102282', '小宜', 'st82st82', '住戶'),
('4110854', '小婷', 'st54st54', '住戶'),
('4125845', '小雯', 'st45st45', '住戶'),
('root', '老藍', 'rootroot', '管理員'),
('super00', '小藍', 'super', '舍監');

-- --------------------------------------------------------

--
-- 資料表結構 `violation`
--

CREATE TABLE `violation` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '違規紀錄編號',
  `household_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → household.id',
  `StID` varchar(20) NOT NULL COMMENT '學號（快取）',
  `v_time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '違規時間',
  `content` varchar(255) NOT NULL COMMENT '違規內容',
  `points` int(11) NOT NULL COMMENT '扣分'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `violation`
--

INSERT INTO `violation` (`id`, `household_id`, `StID`, `v_time`, `content`, `points`) VALUES
(1, 1, '4125845', '2025-11-21 03:25:01', '晚歸未報備', 2),
(2, 2, '4110854', '2025-11-21 03:25:01', '公共區域未清潔', 1),
(3, 3, '4102282', '2025-11-21 03:25:01', '在寢室使用電磁爐', 3);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `household`
--
ALTER TABLE `household`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_household_student_semester` (`StID`,`semester`),
  ADD KEY `idx_household_StID` (`StID`);

--
-- 資料表索引 `sign_in`
--
ALTER TABLE `sign_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_signin_household` (`household_id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`account`);

--
-- 資料表索引 `violation`
--
ALTER TABLE `violation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_violation_household` (`household_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `household`
--
ALTER TABLE `household`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '住宿流水號（主鍵）', AUTO_INCREMENT=4;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `sign_in`
--
ALTER TABLE `sign_in`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '簽到紀錄編號', AUTO_INCREMENT=7;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `violation`
--
ALTER TABLE `violation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '違規紀錄編號', AUTO_INCREMENT=4;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `household`
--
ALTER TABLE `household`
  ADD CONSTRAINT `fk_household_users` FOREIGN KEY (`StID`) REFERENCES `users` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `sign_in`
--
ALTER TABLE `sign_in`
  ADD CONSTRAINT `fk_signin_household` FOREIGN KEY (`household_id`) REFERENCES `household` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `violation`
--
ALTER TABLE `violation`
  ADD CONSTRAINT `fk_violation_household` FOREIGN KEY (`household_id`) REFERENCES `household` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
