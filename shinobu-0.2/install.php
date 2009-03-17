<?php

/* ---

	Copyright (C) 2008 Frank Smit
	http://code.google.com/p/shinobu/

	This file is part of Shinobu.

	Shinobu is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Shinobu is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

error_reporting(E_ALL);
@set_time_limit(0);

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability
header('Content-type: text/html; charset=utf-8');

/* --- Functions
----------------------------------------------------------- */

function utf8_strlen($string)
{
	return strlen(utf8_decode($string));
}

function generate_password($str, $salt)
{
	if (strlen($str) === 0 || strlen($salt) === 0)
		return false;

	return md5($salt).sha1($salt.$str); // Returns a 72 character long hash
}

// Generates a salt
function generate_salt()
{
	$key = NULL;

	for ($x = 0; $x < SALT_LENGTH; ++$x)
		$key .= chr(mt_rand(33, 126));

	return $key;
}

function utf8_ltrim($str, $charlist='')
{
	if($charlist == '')
		return ltrim($str);

	//quote charlist for use in a characterclass
	$charlist = preg_replace('!([\\\\\\-\\]\\[/])!', '\\\${1}', $charlist);

	return preg_replace('/^['.$charlist.']+/u', '', $str);
}

function  utf8_rtrim($str, $charlist='')
{
	if($charlist == '')
		return rtrim($str);

	//quote charlist for use in a characterclass
	$charlist = preg_replace('!([\\\\\\-\\]\\[/])!', '\\\${1}', $charlist);

	return preg_replace('/['.$charlist.']+$/u', '', $str);
}

function  utf8_trim($str, $charlist=" \t\n\r\x0b\xc2\xa0")
{
	if($charlist == '')
		return trim($str);

	return utf8_ltrim(utf8_rtrim($str, $charlist), $charlist);
}

function check_email($email)
{
	if (strlen($email) > 80)
		return false;

	return preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $email);
}

function get_remote_address()
{
	return $_SERVER['REMOTE_ADDR'];
}

function utf8_htmlencode($str)
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function get_microtime($microtime=false)
{
	if ($microtime === false)
		$microtime = microtime();

	list($usec, $sec) = explode(' ', $microtime);
	return ((float)$usec + (float)$sec);
}

/* --- Initialize
----------------------------------------------------------- */

define('SHINOBU', 1);
require './system/core/config.php';
require SYS_CORE_DIR.'/db_layer/'.$db_type.'.php';

$sys_db = new database($db_host, $db_user, $db_password, $db_name, $db_persistent);

// Set variables
$shinobu_version = '0.2.3';
$post_install_errors = false;

// Check PHP version
if (intval(str_replace('.', '', phpversion())) < 5)
	$post_install_errors[] = 'PHP <strong>5</strong> is needed to install Shinobu. You have '.phpversion();

if ($shinobu_version != SHINOBU_VERSION)
	$post_install_errors[] = 'This installer in incompatible with your Shinobu installation. This installer is for Shinobu '.$shinobu_version.' and you have Shinobu '.SHINOBU_VERSION.'.';

// Check if cache folder is writeable
if (!is_writable(SYS_CACHE_DIR))
	$post_install_errors[] = 'The cache folder is not writeable.';

// Check if the tables already exist
if (!isset($_GET['installed']))
{
	if ($sys_db->table_exists(DB_PREFIX.'config'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'config</strong> table already exists in your database.';
	if ($sys_db->table_exists(DB_PREFIX.'content_data'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'content_data</strong> table already exists in your database.';
	if ($sys_db->table_exists(DB_PREFIX.'content_info'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'content_info</strong> table already exists in your database.';
	if ($sys_db->table_exists(DB_PREFIX.'content_types'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'content_types</strong> table already exists in your database.';
	if ($sys_db->table_exists(DB_PREFIX.'navigation'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'navigation</strong> table already exists in your database.';
	if ($sys_db->table_exists(DB_PREFIX.'online'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'online</strong> table already exists in your database.';
	if ($sys_db->table_exists(DB_PREFIX.'usergroups'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'usergroups</strong> table already exists in your database.';
	if ($sys_db->table_exists(DB_PREFIX.'users'))
		$post_install_errors[] = 'The <strong>'.DB_PREFIX.'users</strong> table already exists in your database.';
}

/* --- Installer
----------------------------------------------------------- */

if (isset($_POST['frm-install']))
{
	$form = array_map('utf8_trim', $_POST['form']);
	$errors = false;

	$form['title'] = utf8_htmlencode($form['title']);

	if (empty($form['title']))
		$errors['title'] = 'You have to enter a title for your website.';
	else if (utf8_strlen($form['title']) > 255)
		$errors['title'] = 'The title you entered is too long.';

	if (utf8_strlen($form['description']) > 255)
		$errors['description'] = 'The description you entered is too long.';

	// Check username
	if (empty($form['username']))
		$errors['username'] = 'You have to enter a username.';
	else if (utf8_strlen($form['username']) <= 2)
		$errors['username'] = 'You must enter a longer username.';
	else if (utf8_strlen($form['username']) > 20)
		$errors['username'] = 'The username you entered is too long.';

	// Check e-mail
	if (!empty($form['email']))
	{
		if (!check_email($form['email']))
			$errors['email'] = 'The e-mail address you entered is not valid.';
		else if (strlen($form['email']) > 80)
			$errors['email'] = 'The e-mail address you entered in too long.';
	}
	else
		$errors['email'] = 'You have to enter an e-mail address.';

	// Check password
	if (empty($form['password']))
		$errors['password'] = 'You have to enter a password.';
	else if (utf8_strlen($form['password']) < 6)
		$errors['password'] = 'You must enter a longer password.';

	if ($errors === false)
	{
		$now = time();
		$salt = generate_salt();
		$password = generate_password($form['password'], $salt);
		$hash = sha1(generate_salt());

		// config
		$sys_db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'config` (
  `name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `value` text collate utf8_unicode_ci,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);
		$sys_db->query('INSERT INTO `'.DB_PREFIX.'config` (`name`, `value`) VALUES
(\'website_description\', \''.$sys_db->escape($form['description']).'\'),
(\'theme\', \'simple\'),
(\'website_title\', \''.$sys_db->escape($form['title']).'\'),
(\'welcome_message_body\', \'<p>Shinobu is a website management system aimed to be simple, easy to administrate and easy to extend.</p>\'),
(\'welcome_message_display\', 1),
(\'welcome_message_title\', \'Welcome!\'),
(\'timezone\', 0),
(\'dst\', 1),
(\'allow_username_change\', 1),
(\'default_usergroup\', 3),
(\'visit_timeout\', 300),
(\'user_online_stats\', 1),
(\'admin_theme\', \'default\'),
(\'language\', \'English\'),
(\'show_who_is_online\', 1),
(\'allow_new_registrations\', 1);') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		// usergroups
		$sys_db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'usergroups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `usertitle` varchar(50) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci,
  `p_manage_nav` tinyint(1) unsigned NOT NULL default 0,
  `p_manage_users` tinyint(1) unsigned NOT NULL default 0,
  `p_manage_pages` tinyint(1) unsigned NOT NULL default 0,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);
		$sys_db->query('INSERT INTO `'.DB_PREFIX.'usergroups` (`id`, `name`, `usertitle`, `description`, `p_manage_nav`, `p_manage_users`, `p_manage_pages`) VALUES
(1, \'Guests\', \'Guest\', \'This is the guest group.\', 0, 0, 0),
(2, \'Administrators\', \'Administrator\', \'The admin.\', 1, 1, 1),
(3, \'Members\', \'Member\', \'A member.\', 0, 0, 0);') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		// users
		$sys_db->query('
CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `gid` int(10) unsigned NOT NULL,
  `username` varchar(20) collate utf8_unicode_ci NOT NULL,
  `password` varchar(72) collate utf8_unicode_ci NOT NULL,
  `salt` varchar('.SALT_LENGTH.') collate utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default 0,
  `hash` varchar(40) collate utf8_unicode_ci NOT NULL,
  `real_name` varchar(255) collate utf8_unicode_ci default NULL,
  `description` text collate utf8_unicode_ci,
  `website` varchar(100) collate utf8_unicode_ci default NULL,
  `email` varchar(80) collate utf8_unicode_ci NOT NULL,
  `msn` varchar(100) collate utf8_unicode_ci default NULL,
  `yahoo` varchar(100) collate utf8_unicode_ci default NULL,
  `show_email` tinyint(1) unsigned NOT NULL default 0,
  `language` varchar(25) collate utf8_unicode_ci default NULL,
  `timezone` float NOT NULL default 0,
  `dst` tinyint(1) unsigned NOT NULL default 0,
  `last_login` int(10) unsigned NOT NULL default 0,
  `register_date` int(10) unsigned NOT NULL default 0,
  `registration_ip` varchar(39) collate utf8_unicode_ci NOT NULL default \'0.0.0.0\',
  PRIMARY KEY  (`id`),
  KEY `users_username_idx` (`username`),
  KEY `users_register_date_idx` (`register_date`),
  KEY `users_id_active_idx` (`id`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);
		$sys_db->query('INSERT INTO `'.DB_PREFIX.'users`
(`id`, `gid`, `username`, `password`,
`salt`, `active`, `hash`, `real_name`,
`description`, `website`, `email`, `msn`,
`yahoo`, `show_email`, `language`, `timezone`,
`dst`, `last_login`,
`register_date`, `registration_ip`) VALUES
(1, 1, \'Guest\',
0, 0, 1, 0, \'Mr. Guest\',
NULL, NULL, 0, NULL,
NULL, 0, 0, 0, 0, 0,
0, \'0.0.0.0\'),
(2, 2, \''.$sys_db->escape($form['username']).'\',
\''.$sys_db->escape($password).'\', \''.$sys_db->escape($salt).'\',
1, \''.$sys_db->escape($hash).'\',
\'Mr. Admin\', NULL, \''.$sys_db->escape(WEBSITE_URL).'\',
\''.$sys_db->escape($form['email']).'\', NULL, NULL, 0,
\'English\', 0, 0, 0, '.$now.', \''.$sys_db->escape(get_remote_address()).'\');') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		// content_types
		$sys_db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'content_types` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);
		$sys_db->query('INSERT INTO `'.DB_PREFIX.'content_types` (`id`, `title`) VALUES (1, \'Page\');') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		// content_info
		$sys_db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'content_info` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type_id` int(10) unsigned NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `author` int(10) unsigned NOT NULL,
  `create_date` int(10) unsigned NOT NULL default 0,
  `edit_date` int(10) unsigned NOT NULL default 0,
  `status` tinyint(1) unsigned NOT NULL default 0,
  PRIMARY KEY  (`id`),
  KEY `content_info_status_idx` (`status`),
  KEY `content_info_id_type_idx` (`id`,`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		// content_data
		$sys_db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'content_data` (
  `content_id` int(10) unsigned NOT NULL,
  `type_id` int(10) unsigned NOT NULL,
  `parser` varchar(20) collate utf8_unicode_ci NOT NULL default \'xhtml\',
  `data` text collate utf8_unicode_ci NOT NULL,
  KEY `content_data_content_id` (`content_id`,`type_id`),
  FULLTEXT KEY `content_data_data_flltxt` (`data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		// navigation
		$sys_db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'navigation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `position` tinyint(3) unsigned NOT NULL default 0,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		// online
		$sys_db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'online` (
  `id` int(10) unsigned NOT NULL,
  `username` varchar(20) collate utf8_unicode_ci NOT NULL,
  `token` varchar(40) collate utf8_unicode_ci NOT NULL,
  `last_visit` int(10) unsigned NOT NULL,
  UNIQUE KEY `online_username_unq` (`username`),
  KEY `online_last_visit_idx` (`last_visit`),
  KEY `online_id_idx` (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;') or exit($sys_db->error().'<br /><br />'.__FILE__.'<br />'.__LINE__);

		header('location: '.WEBSITE_URL.'install.php?installed'); exit;
	}
}

/* --- Display
----------------------------------------------------------- */

echo '<?xml version="1.0" encoding="utf-8"?>'."\n";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" xml:lang="en" lang="en" />
	<meta http-equiv="content-language" content="en"/>
	<meta name="robots" content="index, follow"/>
	<meta name="Revisit-after" content="5 days"/>

	<title>Shinobu <?php echo $shinobu_version; ?> Installer</title>

	<style type="text/css">
		/* --- Reset
		---------------------------------------------------------*/

		html, body              { padding:0;margin:0 }
		body					{ background-color: #fff;font-size: 83%;line-height: 1.4em;color: #222 }
		body, input				{ font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Verdana, Arial, sans-serif }

		input			        { padding: 2px;border-width: 1px;border-style: solid;border-color: #7c7c7c #c3c3c3 #ddd }
		input[type="submit"]	{ background: #222;padding: 5px;color: #fff }

		a:link, a:visited		{ color: #A52B2A;text-decoration: underline }
		a:hover, a:active		{ color: #521414 }

		p						{ margin: 1.5em 0 }

		h1,h2					{ font-family: "Chaparral Pro", Georgia, serif;font-weight: normal;color: #444 }
		h1						{ margin-bottom: 0.5em;font-size: 3em;line-height: 1em }
		h2						{ margin: 0.75em 0;font-size: 2em }

		ul						{ margin:0 0.5em 1.5em;padding-left: 2.5em;list-style-type: square }

		hr						{ margin: 0;border-width: 1px 0 0;border-style: dotted;border-color: #0090EC;height: 0px }

		/* --- Misc
		---------------------------------------------------------*/

		#wrapper { margin: 2em auto;width: 500px }
		h1, h2, p { margin-left: 10px;margin-right: 10px }

		label { display: block;float: left;padding: 1px 10px 0 0;width: 100px;text-align: right }
		.clear { clear: both;height: 0px }

		h1 { text-align: center }
		p.fld { background: #f6f6f6;padding: 5px }
		p.fld input { width: 200px }
		p.error { background: #FFB9B9;border: 1px solid #FF6A6A }

		p.error span { display: block;margin-left: 110px }
	</style>
</head>
<body>

	<div id="wrapper">
		<h1>Shinobu <?php echo $shinobu_version; ?> Installer</h1>

		<hr />

		<?php

		if (is_array($post_install_errors))
			echo '<p><strong>Some errors occured:</strong></p>'."\n\n".'<ul><li>'.implode('</li><li>', $post_install_errors).'</li></ul>'."\n\n".'<p>You have to resolve these issues to install Shinobu.</p>';
		else if (isset($_GET['installed']))
			echo '<p>Shinobu has been installed and is now ready to use.</p>'."\n\n".'<p><strong>Do NOT FORGET to remove this file</strong></p>';
		else
		{

		?>

		<p>Complete the form below and then click "Install".</p>

		<p><strong>Note:</strong> your password must be 6 characters or longer.</p>

		<hr />

		<form method="post" accept-charset="utf-8" action="<?php echo WEBSITE_URL; ?>/install.php">

			<h2>Website details</h2>

			<p class="fld<?php echo isset($errors['title']) ? ' error' : NULL; ?>">
				<label>Title:</label>
				<input class="text" type="text" name="form[title]" id="fld-0" maxlength="255" value="Shinobu" />
				<?php echo isset($errors['title']) ? '<span>'.$errors['title'].'</span>' : NULL; ?>
				<span class="clear">&nbsp;</span>
			</p>

			<p class="fld<?php echo isset($errors['description']) ? ' error' : NULL; ?>">
				<label>Description:</label>
				<input class="text" type="text" name="form[description]" id="fld-1" maxlength="255" value="A website management system" />
				<?php echo isset($errors['description']) ? '<span>'.$errors['description'].'</span>' : NULL; ?>
				<span class="clear">&nbsp;</span>
			</p>

			<hr />

			<h2>Admin user</h2>

			<p class="fld<?php echo isset($errors['username']) ? ' error' : NULL; ?>">
				<label>Username:</label>
				<input class="text" type="text" name="form[username]" id="fld-2" maxlength="20" value="Administrator" />
				<?php echo isset($errors['username']) ? '<span>'.$errors['username'].'</span>' : NULL; ?>
				<span class="clear">&nbsp;</span>
			</p>

			<p class="fld<?php echo isset($errors['email']) ? ' error' : NULL; ?>">
				<label>E-mail address:</label>
				<input class="text" type="text" name="form[email]" id="fld-3" maxlength="80" />
				<?php echo isset($errors['email']) ? '<span>'.$errors['email'].'</span>' : NULL; ?>
				<span class="clear">&nbsp;</span>
			</p>

			<p class="fld<?php echo isset($errors['password']) ? ' error' : NULL; ?>">
				<label>Password:</label>
				<input class="password" type="password" name="form[password]" id="fld-4" maxlength="255" />
				<?php echo isset($errors['password']) ? '<span>'.$errors['password'].'</span>' : NULL; ?>
				<span class="clear">&nbsp;</span>
			</p>

			<hr />

			<p>
				<input type="submit" value="Install" name="frm-install" />
			</p>
		</form>

		<?php

		}

		?>
	</div>

</body>
</html>
