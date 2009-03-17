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

class template_parser
{
	public $template;
	private $tags, $tag_data, $addon_tags;

	public function __construct($template_dir)
	{
		if (!is_file($template_dir.'template.tpl'))
			error('Can\'t find template file!', __FILE__, __LINE__);

		$this->template = file_get_contents($template_dir.'template.tpl');
	}

	public function assign($tag_names, $data)
	{
		if (is_array($tag_names) && is_array($tag_names))
		{
			$tag_count = count($tag_names);

			for ($x = 0;$x < $tag_count;$x++)
			{
				$this->tags[$tag_names[$x]] = '[tpl:'.$tag_names[$x].']';
				$this->tag_data[$tag_names[$x]] = $data[$x];
			}
		}
		else
		{
			$this->tags[$tag_names] = '[tpl:'.$tag_names.']'; // Add tag to tag array
			$this->tag_data[$tag_names] = $data; // Add data to data array
		}
	}

	public function add($tag_name, $prefix='', $suffix='')
	{
		if ($this->tag_exists($tag_name))
		{
			if (isset($prefix))
				$this->addon_tags['prefix'][$tag_name][] = $prefix;

			if (isset($suffix))
				$this->addon_tags['suffix'][$tag_name][] = $suffix;
		}

	}

	public function tag_exists($tage_name)
	{
		return preg_match('/\[tpl\:'.$tage_name.'\]/', $this->template);
	}

	public function process()
	{
		// Merge data from the addon tags with the data from the main tags
		if (count($this->addon_tags['prefix']) > 0)
			foreach ($this->addon_tags['prefix'] as $key => $value)
			{
				$value = array_reverse($value); // <- Get the addon tags in the right order
				if (array_key_exists($key, $this->tags))
					foreach ($value as $v)
						$this->tag_data[$key] = $v.$this->tag_data[$key];
			}

		if (count($this->addon_tags['suffix']) > 0)
			foreach ($this->addon_tags['suffix'] as $key => $value)
			{
				if (array_key_exists($key, $this->tags))
					foreach ($value as $v)
						$this->tag_data[$key] = $this->tag_data[$key].$v;
			}

		// Replace tags with tag data
		$this->template = str_replace($this->tags, $this->tag_data, $this->template);
	}
}

?>
