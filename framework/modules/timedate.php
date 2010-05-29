<?php

# =============================================================================
# framework/modules/timedate.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

/* timedate sounds a bit strange, but it's certain that it won't have a name clash
with PHP's DateTime class (http://php.net/manual/en/class.datetime.php) */

class timedate
{
	public $date_format = 'jS, F Y',
	       $time_format = 'H:i';

	public function __construct($timezone)
	{
		$this->set_timezone($timezone);
	}

	public function set_timezone($timezone)
	{
		// See the following URL for a list of supported timezones:
		// http://php.net/manual/en/timezones.php

		return date_default_timezone_set($timezone);
	}

	public function get_timezone($timezone)
	{
		return date_default_timezone_get();
	}

	public function date($timestamp, $format = '')
	{
		// See the following for date formatting information:
		// http://php.net/manual/en/function.date.php

		if (!$format)
			$format = $this->date_format;

		return date($format, $timestamp);
	}

	public function time($timestamp, $format = '')
	{
		// See the following for date formatting information:
		// http://php.net/manual/en/function.date.php

		if (!$format)
			$format = $this->time_format;

		return date($format, $timestamp);
	}
}
