<?php

# =============================================================================
# application/controllers/admin/menu/batch.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class batch_controller extends CmsWebController
{
	public function POST($args)
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'menu'))
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			$this->redirect(url('admin/menu'));

		if (!isset($args['m_items']))
		{
			return tpl::render('redirect', array(
				'redirect_message' => '<p>You have to select atleast one menu item to perform a batch action. '.
									  'You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/menu')
				));
		}

		// A delete action
		if (isset($args['form_delete_selected_m_items']))
		{
			$deleted_row_count = 0;
			$stmt = $this->db->prepare('DELETE FROM '.DB_PREFIX.'menu WHERE id=?');

			foreach ($args['m_items'] as $mid)
			{
				$stmt->bind_param('i', $mid);
				$stmt->execute();
				$deleted_row_count += $stmt->affected_rows;
			}

			$stmt->close();

			cache::clear('main_menu.json');

			return tpl::render('redirect', array(
				'redirect_message' => '<p>'.$deleted_row_count.' menu item(s) successfully deleted. '.
									  'You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/menu')
				));
		}
		else
			$this->redirect(url('admin/menu'));
	}
}
