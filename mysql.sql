--
-- Table structure for table `group_perms`
--

CREATE TABLE IF NOT EXISTS `group_perms` (
  `group_id` int(10) unsigned NOT NULL,
  `perm_id` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `bits` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `perm_id` (`perm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `group_perms`
--

INSERT INTO `group_perms` (`group_id`, `perm_id`, `bits`) VALUES
(1, 'admin_read', 2);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `bits` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `description`, `bits`) VALUES
('admin_read', 'Allow the user to view the administration page index. The access to the other administration components depends on other permissions.', 2);

-- --------------------------------------------------------

--
-- Table structure for table `usergroups`
--

CREATE TABLE IF NOT EXISTS `usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `user_title` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `usergroups`
--

INSERT INTO `usergroups` (`id`, `name`, `user_title`, `description`) VALUES
(1, 'Administrators', 'Administrator', 'Users in this usergroup have no restrictions on the administration panel.'),
(2, 'Members', 'Member', 'Registered users.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL DEFAULT '2',
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_index` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `group_id`, `username`, `password`, `salt`, `hash`, `email`) VALUES
(1, 1, 'Frank', 'd65c4e4ccf8118baf64877e8ad0b8496805525b1', '/z{!j2Xn<q=:I"Tnd;,_', 'ad311415064cf8742acf52f4c731c4f3dccc4769', 'example@example.com'),
(2, 2, 'Nachiko', 'aa283093b9ee1af8004839850cd3fb07f0137838', '=ogRW`f(N0fC^fzcUP&k', 'd1552cc8e84ab3f8dae38f829a2f495faac77ce8', 'lol@example.com');
