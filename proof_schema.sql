--
-- Table structure for table `ci_sessions`
--


CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `user_agent` varchar(120) COLLATE utf8_bin NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


--
-- Table structure for table `login_attempts`
--

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(40) COLLATE utf8_bin NOT NULL,
  `login` varchar(50) COLLATE utf8_bin NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `email` varchar(100) COLLATE utf8_bin NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `ban_reason` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `new_password_key` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `new_password_requested` datetime DEFAULT NULL,
  `new_email` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `new_email_key` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `last_ip` varchar(40) COLLATE utf8_bin NOT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `user_profiles`
--

CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `country` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



--
-- Table structure for table `proof_galleries`
--

CREATE TABLE IF NOT EXISTS `proof_galleries` (
  `user_id` int(11) DEFAULT NULL,
  `gallery_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `gal_id` int(11) NOT NULL AUTO_INCREMENT,
  `open` tinyint(1) DEFAULT '0',
  `gallery_type` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`gal_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `proof_galleries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


--
-- Table structure for table `proof_images`
--

CREATE TABLE IF NOT EXISTS `proof_images` (
  `img_id` varchar(25) COLLATE utf8_bin NOT NULL,
  `gal_id` int(11) NOT NULL,
  `thumb` varchar(25) COLLATE utf8_bin NOT NULL,
  `order` int(11) DEFAULT NULL,
  `original_filename` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`img_id`),
  KEY `gal_id` (`gal_id`),
  CONSTRAINT `proof_images_ibfk_1` FOREIGN KEY (`gal_id`) REFERENCES `proof_galleries` (`gal_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `proof_print_orders`
--

CREATE TABLE IF NOT EXISTS `proof_print_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `filled` tinyint(1) DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `proof_print_sizes`
--

CREATE TABLE IF NOT EXISTS`proof_print_sizes` (
  `size_id` int(11) NOT NULL AUTO_INCREMENT,
  `gal_id` int(11) DEFAULT NULL,
  `size_val` varchar(255) COLLATE utf8_bin NOT NULL,
  `default` tinyint(1) DEFAULT '0',
  `no_input` tinyint(1) NOT NULL DEFAULT '0',
  `price` decimal(11,2) DEFAULT NULL,
  PRIMARY KEY (`size_id`),
  KEY `gal_id` (`gal_id`),
  KEY `size_id` (`size_id`),
  CONSTRAINT `proof_print_sizes_ibfk_1` FOREIGN KEY (`gal_id`) REFERENCES `proof_galleries` (`gal_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `proof_requests`
--

CREATE TABLE IF NOT EXISTS`proof_requests` (
  `req_id` int(11) NOT NULL AUTO_INCREMENT,
  `img_id` varchar(25) COLLATE utf8_bin NOT NULL,
  `size_id` int(11) NOT NULL,
  `num_req` int(11) NOT NULL,
  `submitted` tinyint(1) DEFAULT '0',
  `order_id` int(11) NOT NULL,
  PRIMARY KEY (`req_id`),
  KEY `img_id` (`img_id`),
  KEY `size_id` (`size_id`),
  CONSTRAINT `proof_requests_ibfk_1` FOREIGN KEY (`img_id`) REFERENCES `proof_images` (`img_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `proof_requests_ibfk_2` FOREIGN KEY (`size_id`) REFERENCES `proof_print_sizes` (`size_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `user_autologin`
--

CREATE TABLE IF NOT EXISTS `user_autologin` (
  `key_id` char(32) COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_agent` varchar(150) COLLATE utf8_bin NOT NULL,
  `last_ip` varchar(40) COLLATE utf8_bin NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


