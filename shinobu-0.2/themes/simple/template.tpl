<?xml version="1.0" encoding="[tpl:content_encoding]"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="[tpl:content_direction]">
<head>
	<meta http-equiv="Content-Type" content="[tpl:content_type]; charset=[tpl:content_encoding]" xml:lang="en" lang="en" />
	<meta http-equiv="content-language" content="en"/>
	<meta name="robots" content="index, follow"/>
	<meta name="Revisit-after" content="5 days"/>

	<title>[tpl:page_title]</title>

	<link rel="stylesheet" type="text/css" href="[tpl:theme_path]/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="[tpl:theme_path]/css/main.css" />
</head>
<body>

	<div id="wrapper">

		<noscript>
			<div class="notice">Javascript is disabled in your browser. Some things will not work properly.</div>
		</noscript>

		<div id="header">
			<h1><span>[tpl:website_title]</span></h1>
			<p><span>[tpl:website_description]</span></p>
		</div>

		<div id="main-menu">
			<div class="xbg">
				<div class="tl">
					<div class="tr">
						<div class="bl">
							<div class="br">
[tpl:main_navigation]
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="sidebar">
[tpl:user_block]

[tpl:whoisonline_block]

			<div class="sideblock">
				<h2><span>Links</span></h2>

				<ul>
					<li><a href="http://code.google.com/p/shinobu/">Shinobu</a></li>
					<li><a href="http://61924.wepwnyou.net/">61924</a></li>
				</ul>
			</div>
		</div>

		<div id="body">
			[tpl:main_content]
		</div>

		<div id="footer">
			<div id="poweredby">Powered by <strong>[tpl:software_version]</strong></div>
			<div id="copyright">Copyright 2008 Frank Smit</div>
		</div>
	</div>

	<!-- DEBUG -->

</body>
</html>
