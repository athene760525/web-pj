-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-12-09 08:27:17
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
-- 資料表結構 `dorm_agreement`
--

CREATE TABLE `dorm_agreement` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '流水號',
  `article_no` varchar(20) NOT NULL COMMENT '第幾條',
  `content` text NOT NULL COMMENT '規範內容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `dorm_agreement`
--

INSERT INTO `dorm_agreement` (`id`, `article_no`, `content`) VALUES
(1, '第一條', '說明本住宿生活公約的訂定依據與目的，作為宿舍生活共同遵守的規範。'),
(2, '第二條', '規定住宿生應遵守本公約各項條款，違反者將依規定扣住宿點數。'),
(3, '第三條', '住宿生的獎懲依學生獎懲辦法辦理，由宿舍管理員提報至宿舍服務中心。'),
(4, '第四條', '說明宿舍管理成員的組成，包括管理員、宿舍輔導教官與自治幹部，並由宿舍服務中心統籌督導。'),
(5, '第五條', '各學苑設置服務台，由管理員或受訓自治幹部、工讀生輪值提供服務。'),
(6, '第六條', '服務台開放時間依各宿舍公告為準，寒暑假期間則依實際需要另行公告。'),
(7, '第七條', '住宿生如遇事故可透過服務台、宿舍幹部、管理員或校安中心等管道求助，並依相關SOP處理。'),
(8, '第八條', '宿舍提供入住、退宿與候補等定期作業服務。'),
(9, '第九條', '宿舍提供生活事務協助、維修通報、鑰匙與器材借用、訊息傳遞與公告詢問等不定期服務。'),
(10, '第十條', '依宿舍郵務代收服務辦法，提供包裹、掛號與宅配等郵件代收服務。'),
(11, '第十一條', '住宿生應依規定期限辦理入住與繳費，逾期未繳費且未報備者可能取消床位，延遲補件將按項目扣點。'),
(12, '第十二條', '退宿時需依規定辦理退宿手續、繳清費用、歸還鑰匙並完成寢室檢查，未依規定辦理者得扣除保證金。'),
(13, '第十三條', '遇特殊情況需提早或延後入住、退宿時，應依相關作業原則於期限內提出申請。'),
(14, '第十四條', '未依規定或被勒令退宿者，不退還住宿費與保證金，且不得再申請其他宿舍。'),
(15, '第十五條', '床位排定後不得私自更換或冒名頂替，一經查獲將取消住宿資格並限期搬離。'),
(16, '第十六條', '進出宿舍須使用個人門禁卡，禁止借用或偽造門禁卡與鑰匙，違者扣點或依校規懲處並需賠償。'),
(17, '第十七條', '男女宿均不得留宿非該棟住宿生，違者扣點，累犯得勒令退宿，非該棟或校外人士依校規或報警處理。'),
(18, '第十八條', '女宿全面禁止帶非該棟住宿生進入；男宿在期中末考前後期間亦有限制，違者扣點並得勒令退宿。'),
(19, '第十九條', '禁止帶異性進入住宿區，違反者得勒令退宿，相關人員依校規或法律處理。'),
(20, '第二十條', '服務台不得替非該棟住宿生開門，訪客接待須依規定辦理，違者扣點或得勒令退宿。'),
(21, '第二十一條', '未帶門禁卡者可登記借用，逾一定次數或夜間需人員代為開門時，將依次數扣點。'),
(22, '第二十二條', '不得塗鴉、破壞公共設施或私接電源與更改網路設定，違者扣點並需負修復責任。'),
(23, '第二十三條', '宿舍物品之使用與放置須依物品使用一覽表規定，違者扣點並得沒收保管至退宿時發還。'),
(24, '第二十四條', '為維護公共安全，宿舍內禁止燃燒物品，違反者扣點，情節重大者得勒令退宿並依校規懲處。'),
(25, '第二十五條', '宿舍禁止賭博及打麻將等行為，違者扣點，累犯者得勒令退宿；吸菸、飲酒、鬧事、鬥毆或涉及性平偷拍等行為亦得退宿及懲處。'),
(26, '第二十六條', '住宿生應保持環境安靜，避免喧鬧、談話或使用電器影響他人作息，經檢舉屬實者得扣點。'),
(27, '第二十七條', '宿舍內不得飼養任何動物或寵物，違者扣點。'),
(28, '第二十八條', '禁止在宿舍從事任何私人營利活動，如傳直銷等，違者扣點。'),
(29, '第二十九條', '未經同意不得任意張貼宣傳或競選海報，違規張貼者得扣點。'),
(30, '第三十條', '個人財物須自行妥善保管，發生損失自負責；如有竊盜行為，將依校規與法律處理並勒令退宿。'),
(31, '第三十一條', '住宿生應維護寢室公共財產，人為損壞須照價賠償，無法確認責任人時由同寢室共同負擔，並配合必要之維修與清潔作業。'),
(32, '第三十二條', '全體住宿生有維護宿舍環境整潔的責任，若整潔檢查未通過，相關寢室成員每人將被扣點。'),
(33, '第三十三條', '室內不得打球或進行可能損害門窗與天花板的活動，違者除賠償外並會被扣點。'),
(34, '第三十四條', '新生及新住民有參與安全演練與說明會義務，未依規定出席或補救者將依情形扣點。'),
(35, '第三十五條', '住宿生在幹部執行違規處理時如有態度不佳或妨礙公務之情形，經認定後得扣點。'),
(36, '第三十六條', '違反宿舍郵務代收服務辦法者，將被扣住宿點數。'),
(37, '第三十七條', '會客與交誼活動應於指定公共空間進行，若過度吵鬧且勸導無效者將被扣點。'),
(38, '第三十八條', '除房間外的公共空間不得放置個人物品，經勸導無效仍未移除者將被扣點。'),
(39, '第三十九條', '飲水機與洗手台不得傾倒食物殘渣或堆放垃圾雜物，違者扣點。'),
(40, '第四十條', '使用公共冰箱需遵守各宿舍規定，屢勸不聽者將被扣點。'),
(41, '第四十一條', '洗衣與晾曬衣物須依各宿舍規定使用設備，違規且屢勸不改者將被扣點。'),
(42, '第四十二條', '除寒暑假指定寄放區外，禁止在其他公共空間寄放或堆置行李與私人物品，違者扣點並視同廢棄物處理。'),
(43, '第四十三條', '不得有損害電視、桌椅等公共設備的行為，違者扣點並需負賠償責任。'),
(44, '第四十四條', '宿舍共享空間須依相關使用規則運用，屢勸不聽者將被扣點。'),
(45, '第四十五條', '各宿舍應建立住宿生扣點名冊，由幹部與管理員共同記錄違規情形。'),
(46, '第四十六條', '扣點以住宿期間累計計算，可依銷點程序抵銷，若同項違規累犯則依校規加重處理。'),
(47, '第四十七條', '宿舍幹部依本公約有權對違規住宿生登記扣點或予以其他處分。'),
(48, '第四十八條', '扣點須填寫違規單載明事實與條文，並經相關人員簽核後送交服務台登記。'),
(49, '第四十九條', '扣點名冊應每月清查並公告，內容含學號、違規事項與累計扣點數供查詢。'),
(50, '第五十條', '扣點累計超過一定點數未銷者，將取消下一學期或學年住宿資格，超過較高標準者得退宿並不得再申請。'),
(51, '第五十一條', '退宿處分需經苑代與管理員確認後，通報宿舍服務中心辦理。'),
(52, '第五十二條', '住宿生可透過愛宿服務折抵扣點，每二小時服務時數得抵銷一點。'),
(53, '第五十三條', '雖可銷點但仍保留原違規紀錄，作為日後累犯之認定依據。'),
(54, '第五十四條', '住宿生欲申請銷點須依程序向服務台提出申請並完成愛宿服務與紀錄，由相關人員核章後記入名冊。'),
(55, '第五十五條', '住宿生若不服扣點結果，可在規定期限內向宿舍服務台提出異議。'),
(56, '第五十六條', '宿舍管理單位須在期限內審議異議，對逾期、重複或無理由之異議得予駁回。'),
(57, '第五十七條', '經審議認為異議成立者，應塗銷扣點登記並重新公告名冊。'),
(58, '第五十八條', '對於異議被駁回者，住宿生得於期限內向宿舍服務中心提出申覆，由中心進一步調查決議。'),
(59, '第五十九條', '本公約未盡事宜，各宿舍得依實際情況另訂相關辦法並報宿舍服務中心核備。'),
(60, '第六十條', '本公約經宿舍核心會議通過並由宿舍服務中心核准後實施，修正時亦同。');

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
-- 資料表結構 `penalty`
--

CREATE TABLE `penalty` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '流水號',
  `article_no` varchar(20) NOT NULL COMMENT '條例（第幾條）',
  `content` text NOT NULL COMMENT '違規內容',
  `points` int(11) NOT NULL COMMENT '扣點'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `penalty`
--

INSERT INTO `penalty` (`id`, `article_no`, `content`, `points`) VALUES
(1, '第十一條', '入住時未於規定期限內補繳應交資料者逐項扣住宿點數 1 點。', 1),
(2, '第十六條', '禁止借用或使用他人門禁卡，違規者借用者與使用者各扣 2 點。', 2),
(3, '第二十條', '服務台不得為非該棟住宿生開門；違反者扣 2 點，累犯可退宿。', 2),
(4, '第二十一條', '進出宿舍不帶門禁卡者每學期累計滿 5 次後，自第 6 次起每次扣 2 點。', 2),
(5, '第二十二條', '不得塗鴉、破壞公共設備、私接電源或改動網路設定；違者扣 2 點。', 2),
(6, '第二十六條', '宿舍需保持安靜，影響他人作息經檢舉屬實者扣 2 點。', 2),
(7, '第二十九條', '未經許可張貼宣傳海報或競選海報者，扣 2 點。', 2),
(8, '第三十二條', '寢室整潔複檢未通過者，每位住宿生扣 2 點。', 2),
(9, '第三十三條', '室內打球造成干擾者，除賠償外並扣 2 點。', 2),
(10, '第三十六條', '違反宿舍郵務代收服務辦法者扣 2 點。', 2),
(11, '第三十七條', '公共空間喧鬧影響他人，經勸導無效者扣 2 點。', 2),
(12, '第二十一條', '夜間櫃台無人需請管理員或自治幹部代為開門者，扣 3 點。', 3),
(13, '第二十三條', '違反物品使用一覽表規定，使用或留置禁用品者扣 3 點並沒收。', 3),
(14, '第二十四條', '燃燒物品危及公共安全者扣 3 點，情節重大得退宿並依校規懲處。', 3),
(15, '第二十七條', '宿舍內不得飼養寵物，違者扣 3 點。', 3),
(16, '第二十八條', '宿舍內從事私人營利活動者扣 3 點。', 3),
(17, '第三十四條', '安全演練未出席或未完成補救措施者依情況扣 2~3 點。', 3),
(18, '第三十八條', '公共空間擺放個人物品，勸導無效者扣 3 點。', 3),
(19, '第三十九條', '飲水機、洗手台傾倒食物殘渣或堆放雜物者扣 3 點。', 3),
(20, '第四十條', '冰箱未依規定使用，屢勸不聽者扣 3 點。', 3),
(21, '第四十一條', '違反洗衣與晾曬衣物規定，屢勸不聽者扣 3 點。', 3),
(22, '第四十二條', '未依規定寄放行李，占用公共空間者扣 3 點並視同廢棄物處理。', 3),
(23, '第四十三條', '破壞電視機、桌椅等設備者扣 3 點並需賠償。', 3),
(24, '第四十四條', '違反宿舍共享空間使用規則者屢勸不聽者扣 3 點。', 3),
(25, '第十七條', '男女生宿舍不得留宿非該棟住宿生，違者扣 5 點，累犯可退宿。', 5),
(26, '第十八條', '女宿禁止帶非本棟住宿生進入；男宿於考週前後亦限制來訪，違者扣 5 點。', 5),
(27, '第二十五條', '宿舍禁止賭博、打麻將；違者扣 5 點，累犯退宿；吸菸飲酒鬥毆等嚴重違規亦退宿。', 5),
(28, '第三十五條', '對幹部勸導或扣點程序態度不佳或妨礙作業者扣 5 點。', 5),
(29, '第十五條', '床位排定後私自更換或冒名頂替者，取消住宿資格並限期退宿。', 0),
(30, '第十九條', '禁止帶異性進入住宿區；違者得立即退宿並依法處理。', 0),
(31, '第三十條', '個人物品遭竊須自行負責；竊盜者依法處理並勒令退宿。', 0),
(32, '第十四條', '未依規定或被勒令退宿者，不退還住宿費、保證金且不得再申請。', 0);

-- --------------------------------------------------------

--
-- 資料表結構 `sign_in`
--

CREATE TABLE `sign_in` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '簽到紀錄編號',
  `household_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → household.id',
  `StID` varchar(20) NOT NULL COMMENT '學號',
  `time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '簽到時間',
  `method` enum('舍監登記','住戶登記') NOT NULL COMMENT '簽到方式'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `sign_in`
--

INSERT INTO `sign_in` (`id`, `household_id`, `StID`, `time`, `method`) VALUES
(1, 1, '4125845', '2025-11-21 03:23:14', '住戶登記'),
(2, 2, '4110854', '2025-11-21 03:23:14', '舍監登記'),
(3, 3, '4102282', '2025-11-21 03:23:14', '住戶登記'),
(7, 3, '4102282', '2025-12-09 14:16:15', '住戶登記');

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
('4102282', '小宜', 'st4102282', '住戶'),
('4110854', '小婷', 'st4110854', '住戶'),
('4125845', '小雯', 'st4125845', '住戶'),
('d001', '小藍', 'thd001', '舍監'),
('root', '老藍', 'rootroot', '管理員');

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
-- 資料表索引 `dorm_agreement`
--
ALTER TABLE `dorm_agreement`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `household`
--
ALTER TABLE `household`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_household_student_semester` (`StID`,`semester`);

--
-- 資料表索引 `penalty`
--
ALTER TABLE `penalty`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `sign_in`
--
ALTER TABLE `sign_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_signin_household` (`household_id`);

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
  ADD KEY `fk_violation_household` (`household_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `dorm_agreement`
--
ALTER TABLE `dorm_agreement`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水號', AUTO_INCREMENT=61;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `household`
--
ALTER TABLE `household`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '住宿流水號（主鍵）', AUTO_INCREMENT=4;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `penalty`
--
ALTER TABLE `penalty`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水號', AUTO_INCREMENT=33;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `sign_in`
--
ALTER TABLE `sign_in`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '簽到紀錄編號', AUTO_INCREMENT=8;

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
