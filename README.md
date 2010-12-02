Shinobu 0.4
===========

Shinobu is a simple framework/CMS written in PHP5 and uses MySQLi
to store its data. My intention if not to provide a big, feature overloaded
system, but a simple system that provides some basic features and is easy to
extend (for a PHP developer) and easy to use.

Shinobu is currently under development and it may happen that some
things do not work. And one should not use Shinobu in production.

Download
--------

The latest source code can be downloaded from the repository with
Git.  Just use the following command:

    git clone git://github.com/FSX/shinobu.git

Or download a Zip/Tarball from:

 - Zip: http://github.com/FSX/shinobu/zipball/master
 - Tar: http://github.com/FSX/shinobu/tarball/master

All tagged releases can be found at:

 - http://github.com/FSX/shinobu/downloads

Installation
------------

 1. Create a database for Shinobu.
 2. Import mysql.sql into the database you just created.
 3. Rename `site/sample.config.php` to `site/config.php`
 4. Adjust database and cookie settings in `site/config.php`.
 5. Create a directory, called `cache`, in `site/` and make it writable.
 6. Now run the following commands to install the php-utf8 library:
    `git submodule init` and then `git submodule update`.
 7. Go to the URL where Shinobu is installed.

There are a number of example users imported in the database. All these users
have the same password, `password`.

Requirements
------------

 * A webserver (Nginx, Lighttpd, Apache, etc.)
 * PHP >= 5.2.0 (http://php.net/)
 * MySQLi PHP extension (http://php.net/manual/en/book.mysqli.php)
 * Filter PHP extension (http://php.net/filter)
 * Json PHP extension (http://php.net/manual/en/book.json.php)
 * MySQL 5 (http://www.mysql.com/)

Contact/Support
---------------

You can find me at `#shinobu` on `irc.irchighway.net`.

License
-------

Shinobu is licensed undet the Zlib/PNG license, PHP Markdown under the a
BSD-style open source license and PHP-UTF8 under the GNU/LGPL 2.1.

See the docs directory for all the license files.

Links
-----

 - Git: http://git-scm.com/
 - PHP-UTF8: http://github.com/FSX/php-utf8
 - PHP Markdown & Extra: http://michelf.com/projects/php-markdown/
