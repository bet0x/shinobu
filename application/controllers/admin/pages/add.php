<?php

# =============================================================================
# application/controllers/admin/pages/add.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class add_controller extends CmsWebController
{
	protected $parent_right;

	public function prepare()
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'pages'))
			$this->redirect(SYSTEM_BASE_URL);

		$this->request['args'] = intval($this->request['args']);

		if ($this->request['args'] > 0)
		{
			$result = $this->db->query('SELECT rgt FROM '.DB_PREFIX.'pages WHERE id='.$this->request['args'].' LIMIT 1');

			$page_data = $result->fetch_assoc();
			if (is_null($page_data))
				return $this->send_error(404);
			else
				$this->parent_right = intval($page_data['rgt']);
		}
		else
		{
			$result = $this->db->query('SELECT MAX(rgt) AS rgt FROM '.DB_PREFIX.'pages LIMIT 1');

			$page_data = $result->fetch_assoc();
			if (is_null($page_data))
				$this->parent_right = 0;
			else
				$this->parent_right = intval($page_data['rgt']) + 1;
		}
	}

	public function GET($args)
	{
		return tpl::render('admin_add_page', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new page',
			'subsection' => 'pages',
			'page_id' => $this->request['args'],
			'errors' => array(),
			'values' => array(
				'title' => '',
				'content' => '',
				'is_published' => 0,
				'is_private' => 0,
				'show_toc' => 0,
				'show_meta' => 0)
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_add_page']) || !isset($args['xsrf_token'])
		    || !xsrf::check_cookie($args['xsrf_token']))
			$this->redirect(url('admin/pages'));

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();
		$now = time();

		// Check title
		if (utf8_strlen($args['form']['title']) < 1)
			$errors['title'] = 'The title must be at least 1 character long. Please choose another (longer) title.';
		elseif (utf8_strlen($args['form']['title']) > 255)
			$errors['title'] = 'The title must not be more than 255 characters long. Please choose another (shorter) title.';

		// Check content
		$args['form']['content'] = convert_linebreaks($args['form']['content']);
		if (utf8_strlen($args['form']['content']) < 1)
			$errors['content'] = 'A page can not be empty. Please provide some content.';
		elseif (utf8_strlen($args['form']['content']) > 65535)
			$errors['content'] = 'The page has too much content. Please remove some content.';

		// Check options
		$args['form']['is_published'] = isset($args['form']['is_published']) ? 1 : 0;
		$args['form']['is_private'] = isset($args['form']['is_private']) ? 1 : 0;
		$args['form']['show_toc'] = isset($args['form']['show_toc']) ? 1 : 0;
		$args['form']['show_meta'] = isset($args['form']['show_meta']) ? 1 : 0;

		if (empty($errors))
		{
			$this->db->query('LOCK TABLE '.DB_PREFIX.'pages WRITE');

			$this->db->query('UPDATE '.DB_PREFIX.'pages SET rgt=rgt+2 WHERE rgt >= '.$this->parent_right);
			$this->db->query('UPDATE '.DB_PREFIX.'pages SET lft=lft+2 WHERE lft > '.$this->parent_right);

			$this->db->query('INSERT INTO '.DB_PREFIX.'pages (title, content,
				is_published, is_private, show_toc, show_meta, pub_date, lft, rgt) VALUES(
				"'.$this->db->escape($args['form']['title']).'",
				"'.$this->db->escape($args['form']['content']).'",
				'.$args['form']['is_published'].',
				'.$args['form']['is_private'].',
				'.$args['form']['show_meta'].',
				'.$args['form']['show_meta'].',
				'.$now.',
				'.$this->parent_right.',
				'.($this->parent_right + 1).')');

			$this->db->query('UNLOCK TABLES');

			return tpl::render('redirect', array(
				'redirect_message' => '<p>The page has been successfully added. You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/pages')
				));
		}

		return tpl::render('admin_add_page', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new page',
			'subsection' => 'pages',
			'page_id' => $this->request['args'],
			'errors' => $errors,
			'values' => $args['form']
			));
	}
}
