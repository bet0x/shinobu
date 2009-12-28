<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="<?php echo $redirect_delay ?>;URL=<?php echo u_htmlencode($destination_url) ?>" />

	<title>Redirecting... - <?php echo u_htmlencode($website_title) ?></title>

	<link rel="stylesheet" type="text/css" media="all" href="<?php echo SYSTEM_BASE_URL ?>/static/css/screen.css" />
</head>
<body>

<div class="redirect-box">
	<h1>Redirecting...</h1>

<?php echo $redirect_message ?>

	<p class="skip-redirect"><a href="<?php echo u_htmlencode($destination_url) ?>">Click here if you do not want to wait any longer
	                         (or if your browser does not automatically forward you)</a>.</p>
</div>

</body>
</html>
