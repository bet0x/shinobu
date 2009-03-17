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
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

---  */

class captcha
{
	public $xml_array, $total_items, $rand_item_id, $question;

	public function __construct()
	{
		global $sys_config;

		// Load file, make array and count it
		$this->xml_array = self::object2array(simplexml_load_file(SYS_LANG_DIR.$sys_config['language'].'/questions.xml'));
		$this->xml_array = $this->xml_array['item'];
		$this->total_items = count($this->xml_array) - 1;
	}

	private function get_random_item_id()
	{
		$this->rand_item_id = mt_rand(0, $this->total_items);

		set_cookie('captcha_rand_item_id', $this->rand_item_id, time() + 300);
	}

	public function get_question()
	{
		$this->get_random_item_id();

		// Code from encodeEmailAddress() function in PHP Markdown
		$question = $this->xml_array[$this->rand_item_id]['q'];
		$chars = preg_split('/(?<!^)(?!$)/', $question);
		$seed = (int)abs(crc32($question) / strlen($question)); # Deterministic seed.

		foreach ($chars as $key => $char)
		{
			$ord = ord($char);

			# Ignore non-ascii chars.
			if ($ord < 128)
			{
				$r = ($seed * (1 + $key)) % 100; # Pseudo-random function.
				# roughly 10% raw, 45% hex, 45% dec
				# '@' *must* be encoded. I insist.
				if ($r > 90 && $char != '@') /* do nothing */;
				else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';';
				else              $chars[$key] = '&#'.$ord.';';
			}
		}

		return implode($chars);
	}

	public function check_answer($answer)
	{
		return strtolower($answer) == strtolower($this->xml_array[get_cookie('captcha_rand_item_id')]['a']) ? true : false;
	}

	// Source: http://nl.php.net/manual/en/function.simplexml-load-file.php#56691
	static private function object2array($object)
	{
		$return = NULL;

		if(is_array($object))
			foreach($object as $key => $value)
				$return[$key] = self::object2array($value);
		else
		{
			$var = get_object_vars($object);

			if($var)
				foreach($var as $key => $value)
					$return[$key] = ($key && !$value) ? NULL : self::object2array($value);
			else
				return $object;
		}

		return $return;
	}
}

?>
