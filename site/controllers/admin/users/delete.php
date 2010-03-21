<?php

# =============================================================================
# site/controllers/admin/users/delete.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class delete_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_3))
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($_GET[utils::token()]))
			return $this->send_error(403);

		// Check if user exists
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'users WHERE id='.$this->request['args'].' LIMIT 1')
			or error($this->db->error, __FILE__, __LINE__);

		$user_data = $result->fetch_row();
		if (is_null($user_data))
			return $this->send_error(404);

		// Delete user
		$this->db->query('DELETE FROM '.DB_PREFIX.'users WHERE id='.$this->request['args'])
			or error($this->db->error, __FILE__, __LINE__);

		// Redirect
		return tpl::render('redirect', array(
			'redirect_message' => '<p>User has been successfully removed. You will be redirected to the previous page in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => url('admin/users')
			));
	}
}
