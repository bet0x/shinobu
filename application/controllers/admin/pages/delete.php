<?php

# =============================================================================
# site/controllers/admin/pages/delete.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class delete_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($_GET[xsrf::token()]))
			return $this->send_error(403);

		// Check if page exists
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT lft, rgt, rgt-lft+1 AS width FROM '.DB_PREFIX.'pages WHERE id='.$this->request['args'].' LIMIT 1')
			or error($this->db->error);

		$page_data = $result->fetch_assoc();
		if (is_null($page_data))
			return $this->send_error(404);

		// Delete page an all its children
		$this->db->query('DELETE FROM '.DB_PREFIX.'pages WHERE lft BETWEEN '.$page_data['lft'].' AND '.$page_data['rgt'])
			or error($this->db->error);

		$this->db->query('UPDATE '.DB_PREFIX.'pages SET rgt=rgt-'.$page_data['width'].' WHERE rgt > '.$page_data['rgt'])
			or error($this->db->error);

		$this->db->query('UPDATE '.DB_PREFIX.'pages SET lft=lft-'.$page_data['width'].' WHERE lft > '.$page_data['rgt'])
			or error($this->db->error);

		cache::clear('page_'.$this->request['args'].'.json');

		// Redirect
		return tpl::render('redirect', array(
			'redirect_message' => '<p>The page has been successfully deleted. You will be redirected to the '.
			                      'previous page in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => url('admin/pages')
			));
	}
}
