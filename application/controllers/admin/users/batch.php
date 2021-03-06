<?php

# =============================================================================
# application/controllers/admin/users/batch.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class batch_controller extends CmsWebController
{
	public function POST($args)
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'users'))
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			$this->redirect(url('admin/users'));

		if (!isset($args['users']))
		{
			return tpl::render('redirect', array(
				'redirect_message' => '<p>You have to select atleast one user to perform a batch action. '.
									  'You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/users')
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
					'destination_url' => url('admin/users')
					));
			}

			// Remove users
			$deleted_row_count = 0;
			$stmt = $this->db->prepare('DELETE FROM '.DB_PREFIX.'users WHERE id=?');

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
				'destination_url' => url('admin/users')
				));
		}
		else
			$this->redirect(url('admin/users'));
	}
}
