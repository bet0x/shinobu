<?xml version="1.0" encoding="[tpl:content_encoding]"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="[tpl:content_direction]">
<head>
	<meta http-equiv="Content-Type" content="[tpl:content_type]; charset=[tpl:content_encoding]" xml:lang="en" lang="en" />
	<meta http-equiv="content-language" content="en"/>

	<title>[tpl:page_title]</title>

	<link rel="stylesheet" type="text/css" href="[tpl:theme_path]/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="[tpl:theme_path]/css/main.css" />

</head>
<body>

	<div id="topbar">
		<div>
			<span id="top-links">[tpl:top_links]</span>
			<span id="logo">[tpl:admin_panel_title]</span>
		</div>
	</div>

	<div id="wrapper">

		<noscript>
			<div class="notice">Javascript is disabled in your browser. Some things will not work properly.</div>
		</noscript>

		<div id="menu">
			[tpl:admin_navigation]
		</div>

		<div id="body">
			[tpl:main_content]
		</div>

		<div class="clear">&nbsp;</div>

		<div id="footer">
			<div id="poweredby">Powered by <strong>[tpl:software_version]</strong></div>
			<div id="copyright">Copyright 2008 Frank Smit</div>
		</div>

		<div class="clear">&nbsp;</div>

	</div>

	<!-- DEBUG -->

	[tpl:javascript]

</body>
</html>
