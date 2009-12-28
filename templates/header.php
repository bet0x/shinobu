<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php echo u_htmlencode($page_title) ?> - <?php echo u_htmlencode($website_title) ?></title>

	<link rel="stylesheet" type="text/css" media="all" href="<?php echo SYSTEM_BASE_URL ?>/static/css/screen.css" />
</head>
<body>

<div id="header">
	<div id="main-navigation">
		<ul>
			<?php if (user::$logged_in === true): ?>
			<li class="txts-one"><a href="<?php self::url('user') ?>"><?php echo user::$data['username'] ?> (Profile)</a></li>
			<li>&middot;</li>
			<li><a href="<?php self::url('user/logout') ?>">Log out</a></li>
			<?php else: ?>
			<li><a href="<?php self::url('user/login') ?>">Log in</a></li>
			<li>&middot;</li>
			<li><a href="<?php self::url('user/register') ?>">Register</a></li>
			<?php endif ?>
		</ul>
	</div>

	<h1><span><?php echo u_htmlencode($website_title) ?></span></h1>
</div>

<div id="body">
	<div id="content">
