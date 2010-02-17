--
-- Table structure for table `acl`
--

CREATE TABLE IF NOT EXISTS `acl` (
  `id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `permission_01` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `permission_02` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `permission_03` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `permission_04` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `permission_05` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `permission_06` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `permission_07` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `permission_08` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `acl`
--

INSERT INTO `acl` (`id`, `permission_01`, `permission_02`, `permission_03`, `permission_04`, `permission_05`, `permission_06`, `permission_07`, `permission_08`) VALUES
('administration', 'View administration panel (only view the menu item and administration index)', 'Create, delete and edit pages', 'Delete and edit users', 'Manage menu/navigation items', 'Change system options', 'Manage ACL user groups', 'Manage ACL permissions', '');

-- --------------------------------------------------------

--
-- Table structure for table `acl_groups`
--

CREATE TABLE IF NOT EXISTS `acl_groups` (
  `acl_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `permissions` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `acl_id` (`acl_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `acl_groups`
--

INSERT INTO `acl_groups` (`acl_id`, `group_id`, `permissions`) VALUES
('administration', 1, 1),
('administration', 2, 0);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `users`
--

-- All users have "password" as their login password
INSERT INTO `users` (`id`, `group_id`, `username`, `password`, `salt`, `hash`, `email`) VALUES
(1, 1, 'Frank', 'd65c4e4ccf8118baf64877e8ad0b8496805525b1', '/z{!j2Xn<q=:I"Tnd;,_', 'ad311415064cf8742acf52f4c731c4f3dccc4769', 'example@example.com'),
(2, 2, 'Nachiko', 'aa283093b9ee1af8004839850cd3fb07f0137838', '=ogRW`f(N0fC^fzcUP&k', 'd1552cc8e84ab3f8dae38f829a2f495faac77ce8', 'lol@example.com'),
(3, 2, 'Yuki', 'c6d26c0f5ccbd3924283a2863644de81d48014eb', 'OV_58b7&1199jx\\~h5{$', '751d649fc69acbf191edf129e95a6f3de15a8cf9', 'yuki@example.com');
