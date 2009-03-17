<?php

/* ---

	SimpleText parser 0.1.1, parses simple text
	Copyright 2008 Frank Smit http://61924.wepwnyou.net/

	Some code was taken from the following projects:
	 * http://fluxbb.org/ (./include/parser.php)
	 * http://michelf.com/projects/php-markdown/
	 * http://textile.thresholdstate.com/

    SimpleTextParser is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    SimpleTextParser is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with SimpleTextParser.  If not, see <http://www.gnu.org/licenses/>.

--- */

function parse($str)
{
	$parser = new SimpleText;
	return $parser->parse($str);
}

class SimpleText
{
	public $comment_mode=false;
	protected $hashes = array('hash'=>array(), 'html'=>array());

	public function parse($text, $comment_mode=false)
	{
		$this->comment_mode = $comment_mode;

		// Make safe
		$text = str_replace(array("\r\n", "\r"), "\n", $text);
		$text = preg_replace('/^[ ]+$/m', '', $text);

		// Encode html characters
		$text = $this->html_encode($text);

		// Make hashes of code blocks
		if ($comment_mode === false)
		{
			$pattern = '/(\{{3})(\((.*?)\))?(.*?)(\}{3})/ims';
			$text = preg_replace_callback($pattern, array(&$this, 'cb_hash_code_blocks'), $text);
		}

		// Escape characters
		$text = str_replace(
			array('\{', '\}', '\*', '\#', '\_', '\`', '\^', '\,,', '\~~', '\[', '\]', '\(', '\)', '\\'),
			array('&#123;', '&#125;', '&#42;', '&#35;', '&#95;', '&#39;', '&#94;', '&#44;&#44;', '&#126;&#126;', '&#91;', '&#93;', '&#40;', '&#41;'),
			$text);

		// Make hashes of links and urls
		$pattern = '/\[(\((.*?)\))?((http|https|ftp|irc):\/\/[a-zA-Z0-9._%-?&\/:\#]+)[ ](.*?)\]/i';
		$text = preg_replace_callback($pattern, array(&$this, 'cb_hash_links'), $text);

		$pattern = '/(\((.*?)\))?((http|https|ftp|irc):\/\/[a-zA-Z0-9._%-?&\/:\#]+)/i';
		$text = preg_replace_callback($pattern, array(&$this, 'cb_hash_urls'), $text);

		// Parse headers
		if ($comment_mode === false)
		{
			$pattern = '/^(\={1,6})[ ]*(\((.*?)\))?[ ]*(.*?)[ ]*\=*\n+/m';
			$text = preg_replace_callback($pattern, array(&$this, 'cb_parse_headers'), $text);
		}

		// Parse typefaces - Thank you Textile
		$tf_tags = array('\*', '_', '`', '\^', ',,', '~~');
		$tf_pnct = '.,"\'?!;:';

		foreach ($tf_tags as $t)
		{
            $text = preg_replace_callback('/
                (?:^|(?<=[\s>'.$tf_pnct.'])|([{[]))
                ('.$t.')(?!'.$t.')
                (?::(\S+))?
                ([^\s'.$t.']+|\S[^'.$t.'\n]*[^\s'.$t.'\n])
                (['.$tf_pnct.']*)
                '.$t.'
                (?:$|([\]}])|(?=[[:punct:]]{1,2}|\s))
            /x', array(&$this, 'cd_parse_typeface'), $text);
		}

		// Parse dividers
		if ($comment_mode === false)
		{
			$pattern = '/^(\-{4,})\n+/m';
			$pattern = '/(\-{4,})\n+/s';
			$text = preg_replace($pattern, '</p><hr /><p>', $text);
		}

		// Handle whitespaces
		$text = str_replace(array("\n", "\t", '  ', '  '), array('<br />', '&nbsp; &nbsp; ', '&nbsp; ', ' &nbsp;'), $text);

		// Replace hashes
		if (count($this->hashes['hash']) > 0)
			$text = str_replace($this->hashes['hash'], $this->hashes['html'], $text);

		// Add paragraph tag around text, but make sure there are no empty paragraphs
		$text = preg_replace('#<br />\s*?<br />((\s*<br />)*)#is', "</p>$1<p>", $text);
		$text = str_replace('<p><br />', '<p>', $text);
		$text = str_replace('<p></p>', '', '<p>'.$text.'</p>');

		return $text;
	}

	/* --- Handlers
	------------------------------------------------- */

	protected function handle_attributes($attributes)
	{
		$attributes = explode('|', $attributes);
		$attribute = NULL;

		if (count($attributes) > 1)
			foreach ($attributes as $v)
			{
				list($attr, $value) = explode('=', $v);

				if (in_array($attr, array('id', 'class', 'rel', 'title')))
					$attribute .= ' '.$attr.'="'.$value.'"';
			}
		else
		{
			list($attr, $value) = explode('=', $attributes[0]);

			if (in_array($attr, array('id', 'class', 'rel', 'title')))
				$attribute = ' '.$attr.'="'.$value.'"';
		}

		return $attribute;
	}

	/* --- Callback functions
	------------------------------------------------- */

	protected function cd_parse_typeface($matches)
	{
		$tags = array(
			'*' => 'strong',
			'_' => 'em',
			'`' => 'code',
			'^' => 'sup',
			',,' => 'sub',
			'~~' => 'del'
			);

		$tag = $tags[$matches[2]];

		return '<'.$tag.'>'.$matches[4].'</'.$tag.'>';
	}

	protected function cb_parse_headers($matches)
	{
		$attributes = NULL;
		$level = strlen($matches[1]);

		if (!empty($matches[3]) && $this->comment_mode === false)
			$attributes = $this->handle_attributes($matches[3]);

		return '</p><h'.$level.$attributes.'>'.$matches[4].'</h'.$level.'><p>';
	}

	protected function cb_hash_code_blocks($matches)
	{
		$code_block_hash = md5($matches[0].mt_rand());
		$attributes = NULL;

		if (!empty($matches[3]) && $this->comment_mode === false)
			$attributes = $this->handle_attributes($matches[3]);

		$this->hashes['hash'][] = $code_block_hash;
		$this->hashes['html'][] = '</p><pre'.$attributes.'><code>'.trim(str_replace("\t", '&nbsp; &nbsp; ', $matches[4]), "\n").'</code></pre><p>';

		return $code_block_hash;
	}

	protected function cb_hash_links($matches)
	{
		$link_hash = md5($matches[0].mt_rand());
		$attribute = NULL;
		$this->hashes['hash'][] = $link_hash;

		if (!empty($matches[2]) && $this->comment_mode === false)
			$attributes = $this->handle_attributes($matches[2]);

		if (!in_array($this->get_ext($matches[5]), array('png', 'jpg', 'gif')))
			$this->hashes['html'][] = '<a'.$attributes.' href="'.$matches[3].'">'.$matches[5].'</a>';
		else
			$this->hashes['html'][] = '<a'.$attributes.' href="'.$matches[3].'"><img src="'.$matches[5].'" alt="" /></a>';

		return $link_hash;
	}

	protected function cb_hash_urls($matches)
	{
		$url_hash = md5($matches[0].mt_rand());
		$attributes = NULL;
		$this->hashes['hash'][] = $url_hash;

		if (!empty($matches[2]) && $this->comment_mode === false)
			$attributes = $this->handle_attributes($matches[2]);

		if (!in_array($this->get_ext($matches[3]), array('png', 'jpg', 'gif')))
			$this->hashes['html'][] = '<a'.$attributes.' href="'.$matches[3].'">'.$matches[3].'</a>';
		else
			$this->hashes['html'][] = '<img'.$attributes.' src="'.$matches[3].'" alt="" />';

		return $url_hash;
	}

	/* --- String manipulation functions
	------------------------------------------------- */

	// Encode html
	function html_encode($str)
	{
		return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
	}

	// Get extension from image
	protected function get_ext($filepath)
	{
		$explode = explode('.', $filepath);
		return $explode[count($explode)-1];
	}
}

?>
