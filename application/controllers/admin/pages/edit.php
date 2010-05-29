<?php

# =============================================================================
# site/controllers/admin/pages/edit.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class edit_controller extends CmsWebController
{
	private $_page_data = null;

	public function prepare()
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);

		// Get page information
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT p.id, p.title, p.content, p.is_published, p.is_private, p.pub_date,
			p.edit_date, p.show_toc, p.show_meta
			FROM '.DB_PREFIX.'pages AS p WHERE p.id='.$this->request['args'].' LIMIT 1')
			or error($this->db->error);

		$this->_page_data = $result->fetch_assoc();
		if (is_null($this->_page_data))
			return $this->send_error(404);

		$this->load_timedate();
		$this->_page_data['pub_date'] = $this->timedate->date($this->_page_data['pub_date']);
		$this->_page_data['edit_date'] = $this->_page_data['edit_date'] != '0' ?
			$this->timedate->date($this->_page_data['edit_date']) : null;
	}

	public function GET($args)
	{
		return tpl::render('admin_edit_page', array(
			'website_section' => 'Administration',
			'page_title' => 'Edit page: '.$this->_page_data['title'],
			'subsection' => 'pages',
			'admin_perms' => $this->user->get_acl('administration'),
			'errors' => array(),
			'values' => $this->_page_data
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_edit_page']))
			$this->redirect(url('admin/pages'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

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
			$this->db->query('UPDATE '.DB_PREFIX.'pages SET
				title="'.$this->db->escape($args['form']['title']).'",
				content="'.$this->db->escape($args['form']['content']).'",
				is_published='.$args['form']['is_published'].',
				is_private='.$args['form']['is_private'].',
				show_toc='.$args['form']['show_toc'].',
				show_meta='.$args['form']['show_meta'].',
				edit_date='.$now.' WHERE id='.$this->request['args'])
				or error($this->db->error);

			cache::clear('page_'.$this->request['args'].'.json');

			return tpl::render('redirect', array(
				'redirect_message' => '<p>The page has been successfully updated. You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/pages')
				));
		}

		return tpl::render('admin_edit_page', array(
			'website_section' => 'Administration',
			'page_title' => 'Edit page: '.$this->_page_data['title'],
			'subsection' => 'pages',
			'admin_perms' => $this->user->get_acl('administration'),
			'errors' => array(),
			'values' => $this->_page_data
			));
	}
}
