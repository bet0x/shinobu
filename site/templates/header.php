<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php echo u_htmlencode($page_title), (isset($website_section) ? ' - '.u_htmlencode($website_section) : ''), ' - ', u_htmlencode($website_title) ?></title>

	<link rel="stylesheet" type="text/css" media="all" href="<?php echo static_url('css/screen.css') ?>" />
</head>
<body>

<div id="header">
	<h1>
		<a href="<?php echo SYSTEM_BASE_URL ?>"><?php echo u_htmlencode($website_title) ?></a>
		<span><?php echo isset($website_section) ? u_htmlencode($website_section) : u_htmlencode($page_title) ?></span>
	</h1>

	<div id="main-navigation">
		<?php if (isset($main_menu[0])): ?>
		<ul>
			<?php

			foreach ($main_menu as $k => $item)
			{
				if ($k !== 0) echo '<li>&middot;</li>', "\n";
				echo '<li><a href="', $item['path'], '">', $item['name'], '</a></li>', "\n";
			}

			?>
		</ul>
		<?php endif ?>

		<ul>
			<?php if ($authenticated): ?>
			<li><a href="<?php echo url('user') ?>"><?php echo u_htmlencode($username) ?> (Profile)</a></li>
			<li>&middot;</li>
			<?php if ($admin_view): ?>
			<li><a href="<?php echo url('admin') ?>">Administration</a></li>
			<li>&middot;</li>
			<?php endif ?>
			<li><a href="<?php echo url('user/logout'), '&amp;', xsrf::token() ?>">Log out</a></li>
			<?php else: ?>
			<li><a href="<?php echo url('user/login') ?>">Log in</a></li>
			<li>&middot;</li>
			<li><a href="<?php echo url('user/register') ?>">Register</a></li>
			<?php endif ?>
		</ul>
	</div>
</div>

<div id="body">
	<div id="content">
