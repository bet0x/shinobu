<?php

# =============================================================================
# site/controllers/page.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class page_controller extends CmsWebController
{
	public function GET($args)
	{
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT p.id, p.title, p.content, p.is_private, p.show_meta, p.pub_date, p.edit_date, u.username AS author
			FROM '.DB_PREFIX.'pages AS p LEFT JOIN '.DB_PREFIX.'users AS u ON u.id=p.author_id
			WHERE p.id='.$this->request['args'].' AND p.is_published=1 LIMIT 1')
			or error($this->db->error, __FILE__, __LINE__);

		$page_data = $result->fetch_assoc();
		if (is_null($page_data))
			return $this->send_error(404);

		if ($page_data['is_private'] == 1 && !$this->user->authenticated())
			$this->redirect(url('user/login'));

		if ($page_data['show_meta'] == '1')
		{
			$this->load_timedate();
			$page_data['pub_date'] = $this->timedate->date($page_data['pub_date']);
			$page_data['edit_date'] = $page_data['edit_date'] != '0' ? $this->timedate->date($page_data['edit_date']) : null;
		}

		require SYS_LIB.'/markdown/markdown.php';
		$page_data['content'] = Markdown($page_data['content']);

		return tpl::render('page', array(
			'page_title' => $page_data['title'],
			'page_data' => $page_data,
			));
	}
}
