<?php

# =============================================================================
# application/controllers/admin/groups/delete.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class delete_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'groups'))
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($_GET[xsrf::token()]))
			$this->redirect(url('admin/groups'));

		// Check if group exists
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT COUNT(u.group_id) FROM '.DB_PREFIX.'usergroups AS g LEFT JOIN '.DB_PREFIX.'users AS u
			ON u.group_id=g.id WHERE g.id='.$this->request['args'].' GROUP BY g.id LIMIT 1');

		$member_count = $result->fetch_offset();
		if (is_null($member_count))
			return $this->send_error(404);

		// Check if group has any members before it's deleted
		if ($member_count == '0')
		{
			$this->db->query('DELETE FROM '.DB_PREFIX.'usergroups WHERE id='.$this->request['args']);
			$this->db->query('DELETE FROM '.DB_PREFIX.'permissions WHERE group_id='.$this->request['args']);

			$redirect_message = 'Usergroup has been successfully removed.';
		}
		else
			$redirect_message = 'This usergroup has members. Only groups that do not have any members can be removed.';

		// Redirect
		return tpl::render('redirect', array(
			'redirect_message' => '<p>'.$redirect_message.' You will be redirected to the '.
			                      'previous page in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => url('admin/groups')
			));
	}
}
