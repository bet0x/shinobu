<?php

# =============================================================================
# site/controllers/admin/menu/delete.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class delete_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_4))
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($_GET[xsrf::token()]))
			return $this->send_error(403);

		// Check if menu item exists
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'menu WHERE id='.$this->request['args'].' LIMIT 1')
			or error($this->db->error);

		$user_data = $result->fetch_row();
		if (is_null($user_data))
			return $this->send_error(404);

		// Delete menu item
		$this->db->query('DELETE FROM '.DB_PREFIX.'menu WHERE id='.$this->request['args'])
			or error($this->db->error);

		cache::clear('main_menu.json');

		// Redirect
		return tpl::render('redirect', array(
			'redirect_message' => '<p>Menu item has been successfully deleted. You will be redirected to the '.
			                      'previous page in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => url('admin/menu')
			));
	}
}
