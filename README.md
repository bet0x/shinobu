Shinobu 0.4
===========

Shinobu is a minimalistic framework/CMS written in PHP5 and uses MySQLi
to store its data. My intention if not to provide a big, feature overloaded
system, but a simple system that provides some basic features and is easy to
extend (for a PHP developer) and easy to use.

Note: Shinobu is currently under development and it may happen that some
things do not work.

Download
--------

The latest source code can be downloaded from the repository with
Git.  Just use the following command:

    git clone git://github.com/FSX/shinobu.git

An archived version can also be downloaded from http://github.com/FSX/shinobu.

Installation
------------

 1. Import mysql.sql into your database.
 2. Rename include/sample.config.php to include/config.php
 3. Adjust all the needed variables in include/config.php

Requirements
------------

 * A webserver (Nginx, Lighttpd, Apache, etc.)
 * PHP >= 5.2.0 (http://php.net/)
 * MySQLi (http://php.net/manual/en/book.mysqli.php)
 * Filter (http://php.net/filter)

Contact/Support
---------------

You can find me at #shinobu on irc.irchighway.net.

License
-------

Shinobu is licensed undet the Zlib/PNG license, PHP Markdown under the a
BSD-style open source license and PHP-UTF8 under the GNU/LGPL 2.1.

See the COPYING file for the full licenses.

Links
-----

 - Git: http://git-scm.com/
 - PHP-UTF8: http://github.com/FSX/php-utf8
 - PHP Markdown & Extra: http://michelf.com/projects/php-markdown/
