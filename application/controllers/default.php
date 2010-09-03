<?php

# =============================================================================
# application/controllers/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if ($this->config->default_homepage === 0)
		{
			return tpl::render('home', array(
				'page_title' => 'Home',
				'page_body' => '<p>Hello, world!</p>',
				));
		}

		if (($homepage_html = cache::read('page_'.$this->config->default_homepage)))
		{
			return tpl::render('home', array(
				'page_title' => 'Home',
				'page_body' => '<h2>'.u_htmlencode($homepage_html['title']).'</h2>'."\n\n".$homepage_html['content'],
				));
		}

		// Fetch page and parse contents
		$result = $this->db->query('SELECT p.id, p.title, p.content, p.is_private, p.show_toc, p.show_meta, p.pub_date, p.edit_date
			FROM '.DB_PREFIX.'pages AS p
			WHERE p.id='.$this->config->default_homepage.' AND p.is_published=1 AND p.is_private=0 LIMIT 1');

		$page_data = $result->fetch_assoc();
		if (!is_null($page_data))
		{
			if ($page_data['show_meta'] == '1')
			{
				$this->load_timedate();
				$page_data['pub_date'] = $this->timedate->date($page_data['pub_date']);
				$page_data['edit_date'] = $page_data['edit_date'] != '0' ? $this->timedate->date($page_data['edit_date']) : null;
			}

			require SYS_LIB.'/markdown/markdown.php';
			$page_data['content'] = Markdown($page_data['content']);

			// Table of contents
			if ($page_data['show_toc'] == '1')
				$page_data['content'] = generate_toc($page_data['content']);

			// Breadcrumbs
			$result = $this->db->query('SELECT parent.id, parent.title FROM '.DB_PREFIX.'pages AS node,
				'.DB_PREFIX.'pages AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.id='.$this->config->default_homepage.' ORDER BY parent.lft');

			$page_data['breadcrumbs'][] = '<a href="'.SYSTEM_BASE_URL.'">Home</a>';
			while ($row = $result->fetch_assoc())
			{
				if ($row['id'] != $this->request['args'])
					$page_data['breadcrumbs'][] = '<a href="'.url('page:'.$row['id']).'">'.u_htmlencode($row['title']).'</a>';
				else
					$page_data['breadcrumbs'][] = '<span class="you-are-here">'.u_htmlencode($row['title']).'</span>';
			}

			$page_data['breadcrumbs'] = implode(' &#187; ', $page_data['breadcrumbs']);

			$homepage_html = '<h2>'.u_htmlencode($page_data['title']).'</h2>'."\n\n".$page_data['content'];
			cache::write('page_'.$this->config->default_homepage, $page_data);
		}
		else
			$homepage_html = '<p>Hello, world!</p>';

		return tpl::render('home', array(
			'page_title' => 'Home',
			'page_body' => $homepage_html,
			));
	}
}
