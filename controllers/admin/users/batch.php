<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class batch_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_3))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function POST($args)
	{
		/* The first thing to check, when a delete action is being send, is if the
		user is trying to delete himself. This should not be possible or the user
		will be able to delete all users, which is not very good. */

		if (!isset($args['xsrf_token']) || !utils::check_xsrf_cookie($args['xsrf_token']))
			return $this->send_error(403);

		if (!isset($args['users']))
		{
			return tpl::render('redirect', array(
				'redirect_message' => '<p>You have to select atleast one user to perform a batch action. You will be redirected to the '.
									  'previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => utils::url('admin/users')
				));
		}

		// A delete action
		if (isset($args['form_delete_selected_users']))
		{
			// Check if the user tries to delete himself
			if (in_array($this->user->data['id'], $args['users']))
			{
				return tpl::render('redirect', array(
					'redirect_message' => '<p>You can not delete yourself... You will be redirected to the '.
										  'previous page in 2 seconds.</p>',
					'redirect_delay' => 2,
					'destination_url' => utils::url('admin/users')
					));
			}

			// Remove users
			$deleted_row_count = 0;
			$stmt = $this->db->prepare('DELETE FROM '.DB_PREFIX.'users WHERE id=?')
				or error($this->db->error, __FILE__, __LINE__);

			foreach ($args['users'] as $uid)
			{
				$stmt->bind_param('i', $uid);
				$stmt->execute();
				$deleted_row_count += $stmt->affected_rows;
			}

			$stmt->close();

			return tpl::render('redirect', array(
				'redirect_message' => '<p>'.$deleted_row_count.' user(s) successfully deleted. You will be redirected to the '.
									  'previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => utils::url('admin/users')
				));
		}
		else
			$this->redirect(utils::url('admin/users'));
	}
}
