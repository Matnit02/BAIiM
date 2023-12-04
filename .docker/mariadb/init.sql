-- maciek id: 1000
-- manager id: 200
-- admin id: 100
-- user1 id: 1
-- jarek id: 2



DROP TABLE IF EXISTS `active_patterns`;
CREATE TABLE IF NOT EXISTS `active_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `email_pattern` int(11) NOT NULL,
  `description` tinytext COLLATE utf8_bin DEFAULT NULL,
  `revision_num` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


REPLACE INTO `active_patterns` (`id`, `name`, `email_pattern`, `description`, `revision_num`) VALUES
	(1, 'Default', 1, 'Setting pattern use by default', 3),
	(2, 'Daily', 2, 'Zazwyczaj gdy nie ma problemow', 455);

DROP TABLE IF EXISTS `active_requests`;
CREATE TABLE IF NOT EXISTS `active_requests` (
  `status` int(1) NOT NULL DEFAULT 1,
  `eid` varchar(8) COLLATE utf8_bin NOT NULL,
  `bid` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `bsid` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `building3` tinyint(1) DEFAULT 0,
  `building2` tinyint(1) DEFAULT 0,
  `building1` tinyint(1) DEFAULT 0,
  `room4` tinyint(1) DEFAULT 0,
  `room3` tinyint(1) DEFAULT 0,
  `room2` tinyint(1) DEFAULT 0,
  `room1` tinyint(1) DEFAULT 0,
  `room4_justification` text COLLATE utf8_bin DEFAULT NULL,
  `room3_justification` text COLLATE utf8_bin DEFAULT NULL,
  `room2_justification` text COLLATE utf8_bin DEFAULT NULL,
  `room1_justification` text COLLATE utf8_bin DEFAULT NULL,
  `admin_comment` text COLLATE utf8_bin DEFAULT NULL,
  `activaiton_day` date DEFAULT NULL,
  `upload_cer_time` date DEFAULT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


REPLACE INTO `active_requests` (`status`, `eid`, `bid`, `bsid`, `building3`, `building2`, `building1`, `room4`, `room3`, `room2`, `room1`, `room4_justification`, `room3_justification`, `room2_justification`, `room1_justification`, `admin_comment`, `activaiton_day`, `upload_cer_time`) VALUES
	(1, '2', NULL, NULL, 1, 1, 0, 0, 1, 1, 1, NULL, NULL, NULL, NULL, '', '2023-10-16', NULL),
	(1, '1000', NULL, NULL, 1, 1, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, 'User added via script to sync db 2023-12-01', '2023-12-01', NULL);


DROP TABLE IF EXISTS `email_patterns`;
CREATE TABLE IF NOT EXISTS `email_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_name` tinytext COLLATE utf8_bin NOT NULL,
  `admin_email` tinytext COLLATE utf8_bin NOT NULL,
  `reception_email` tinytext COLLATE utf8_bin NOT NULL,
  `manager_email` tinytext COLLATE utf8_bin DEFAULT NULL,
  `user_email` tinytext COLLATE utf8_bin DEFAULT NULL,
  `description` tinytext COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

REPLACE INTO `email_patterns` (`id`, `pattern_name`, `admin_email`, `reception_email`, `manager_email`, `user_email`, `description`) VALUES
	(1, 'DEFAULT', 'maciek_szefito@example.com', 'maciek_szefito@example.com', 'maciek_szefito@example.com', 'maciek_szefito@example.com', 'Setting pattern use by default'),
	(2, 'MOI PRACOWNICY', 'boss@example.com', 'krzysztof@example.com', 'maetusz@example.com', 'natalia@example.com', 'pracusie');

DROP TABLE IF EXISTS `g_user_bindings`;
CREATE TABLE IF NOT EXISTS `g_user_bindings` (
  `eid` varchar(8) COLLATE utf8_bin NOT NULL,
  `bind_varible` varchar(20) COLLATE utf8_bin DEFAULT '',
  `in_use` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `access`;
CREATE TABLE IF NOT EXISTS `access` (
  `eid` varchar(8) COLLATE utf8_bin NOT NULL,
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `login` varchar(32) COLLATE utf8_bin NOT NULL,
  `email` varchar(48) COLLATE utf8_bin NOT NULL,
  `training_date1` date DEFAULT NULL,
  `status1` tinyint(1) DEFAULT 0,
  `training_date2` date DEFAULT NULL,
  `status2` tinyint(1) DEFAULT 0,
  `comments` text COLLATE utf8_bin DEFAULT '',
  `manager_id` varchar(8) COLLATE utf8_bin DEFAULT '',
  `delegated_manager_id` varchar(8) COLLATE utf8_bin DEFAULT '',
  `rights` varchar(6) COLLATE utf8_bin DEFAULT '',
  PRIMARY KEY (`eid`) USING BTREE,
  UNIQUE KEY `EMAIL` (`email`),
  UNIQUE KEY `NSN_LOGIN` (`login`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

REPLACE INTO `access` (`eid`, `name`, `login`, `email`, `training_date1`, `status1`, `training_date2`, `status2`, `comments`, `manager_id`, `delegated_manager_id`, `rights`) VALUES
	('1000', 'Maciek SZEF', 'maciek', 'macie@example.com', NULL, 0, NULL, 0, '', '200', '', 'lmwg'),
	('100', 'Mateusz Setkowicz', 'admin', 'admin@example.com', NULL, 0, NULL, 0, '', '1000', '', 'l'),
	('200', 'Krzysztof Stefanski', 'manager', 'manager@example.com', NULL, 0, NULL, 0, '', '1000', '', 'm'),
	('1', 'User1', 'user1', 'user1@example.com', NULL, 0, NULL, 0, '', '200', '', ''),
	('2', 'Jarek Kozak', 'jarek', 'jarek.kozak@example.com', '2024-11-05', 1, '2024-11-05', 1, 'Przykadowoy Uzytkownik', '200', '', ''),
  ('3', 'User3', 'user3', 'user3@example.com', '2024-11-03', 1, '2024-11-03', 1, '', '200', '', ''),
	('4', 'User4', 'user4', 'user4@example.com', '2024-11-04', 1, '2024-11-04', 1, '', '200', '', ''),
	('5', 'User5', 'user5', 'user5@example.com', '2024-11-05', 1, '2024-11-05', 1, '', '200', '', ''),
	('6', 'User6', 'user6', 'user6@example.com', '2024-11-06', 1, '2024-11-06', 1, '', '200', '', ''),
	('7', 'User7', 'user7', 'user7@example.com', '2024-11-07', 1, '2024-11-07', 1, '', '200', '', ''),
	('8', 'User8', 'user8', 'user8@example.com', '2024-11-08', 1, '2024-11-08', 1, '', '200', '', '');
	

DROP TABLE IF EXISTS `autodelegation`;
CREATE TABLE IF NOT EXISTS `autodelegation` (
  `eid` varchar(50) NOT NULL DEFAULT '',
  `delegated_id` varchar(50) DEFAULT NULL,
  `in_use` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `requests`;
CREATE TABLE IF NOT EXISTS `requests` (
  `status` int(1) NOT NULL DEFAULT 0,
  `eid` varchar(8) COLLATE utf8_bin NOT NULL,
  `bid` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `bsid` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `training_attendance` tinyint(1) DEFAULT 0,
  `ohs` tinyint(1) DEFAULT 0,
  `medical` tinyint(1) DEFAULT 0,
  `building3` tinyint(1) DEFAULT 0,
  `building2` tinyint(1) DEFAULT 0,
  `building1` tinyint(1) DEFAULT 0,
  `room4` tinyint(1) DEFAULT 0,
  `room3` tinyint(1) DEFAULT 0,
  `room2` tinyint(1) DEFAULT 0,
  `room1` tinyint(1) DEFAULT 0,
  `room4_justification` text COLLATE utf8_bin DEFAULT NULL,
  `room3_justification` text COLLATE utf8_bin DEFAULT NULL,
  `room2_justification` text COLLATE utf8_bin DEFAULT NULL,
  `room1_justification` text COLLATE utf8_bin DEFAULT NULL,
  `admin_comment` text COLLATE utf8_bin DEFAULT NULL,
  `manager_comment` text COLLATE utf8_bin DEFAULT NULL,
  `accept_manager_name` text COLLATE utf8_bin DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  PRIMARY KEY (`eid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


REPLACE INTO `requests` (`status`, `eid`, `bid`, `bsid`, `training_attendance`, `ohs`, `medical`, `building3`, `building2`, `building1`, `room4`, `room3`, `room2`, `room1`, `room4_justification`, `room3_justification`, `room2_justification`, `room1_justification`, `admin_comment`, `manager_comment`, `accept_manager_name`, `request_date`) VALUES
	(8, '2', '82865', '4546', 1, 1, 1, 1, 1, 0, 0, 1, 1, 1, '', 'I need access due to current duties', 'I need access due to current duties', 'I need access due to current duties', '', '', 'Krzysztof Stefanski', '2023-11-16'),
	(5, '3', 'bid3', 'bsid3', 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 'Sth', 'Sth', 'Sth', 'Sth', '', '', 'Krzysztof Stefanski', '2023-11-13'),
	(5, '4', 'bid4', 'bsid4', 0, 0, 0, 0, 1, 1, 1, 1, 1, 0, 'Sth', 'Sth', 'Sth', '', '', '', 'Krzysztof Stefanski', '2023-11-14'),
	(5, '5', 'bid5', 'bsid5', 0, 0, 0, 1, 1, 1, 1, 1, 0, 0, 'Sth', 'Sth', '', '', '', '', 'Krzysztof Stefanski', '2023-11-15'),
	(2, '6', 'bid6', 'bsid6', 0, 0, 1, 1, 1, 1, 1, 0, 0, 0, 'Sth', '', '', '', '', '', 'Krzysztof Stefanski', '2023-11-16'),
	(2, '7', 'bid7', 'bsid7', 0, 1, 1, 1, 1, 1, 0, 0, 0, 0, '', '', '', '', '', '', 'Krzysztof Stefanski', '2023-11-17'),
	(2, '8', 'bid7', 'bsid7', 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, '', '', '', '', '', '', 'Krzysztof Stefanski', '2023-11-17');



