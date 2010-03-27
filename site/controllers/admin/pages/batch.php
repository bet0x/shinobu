<?php

# =============================================================================
# site/controllers/admin/pages/batch.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class batch_controller extends CmsWebController
{
	public function POST($args)
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

		if (!isset($args['pages']))
		{
			return tpl::render('redirect', array(
				'redirect_message' => '<p>You have to select atleast one page to perform a batch action. '.
									  'You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/pages')
				));
		}

		// A delete action
		if (isset($args['form_delete_selected_pages']))
		{
			$deleted_row_count = 0;
			$stmt = $this->db->prepare('DELETE FROM '.DB_PREFIX.'pages WHERE id=?')
				or error($this->db->error, __FILE__, __LINE__);

			foreach ($args['pages'] as $pid)
			{
				$stmt->bind_param('i', $pid);
				$stmt->execute();
				$deleted_row_count += $stmt->affected_rows;
			}

			$stmt->close();

			return tpl::render('redirect', array(
				'redirect_message' => '<p>'.$deleted_row_count.' page(s) successfully deleted. '.
									  'You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/pages')
				));
		}
		elseif (isset($args['form_publish_selected_pages']))
		{
			$changed_row_count = 0;
			$stmt = $this->db->prepare('UPDATE '.DB_PREFIX.'pages SET is_published=1 WHERE id=?')
				or error($this->db->error, __FILE__, __LINE__);

			foreach ($args['pages'] as $pid)
			{
				$stmt->bind_param('i', $pid);
				$stmt->execute();
				$changed_row_count += $stmt->affected_rows;
			}

			$stmt->close();

			return tpl::render('redirect', array(
				'redirect_message' => '<p>'.$changed_row_count.' page(s) successfully published. '.
									  'You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/pages')
				));
		}
		elseif (isset($args['form_unpublish_selected_pages']))
		{
			$changed_row_count = 0;
			$stmt = $this->db->prepare('UPDATE '.DB_PREFIX.'pages SET is_published=0 WHERE id=?')
				or error($this->db->error, __FILE__, __LINE__);

			foreach ($args['pages'] as $pid)
			{
				$stmt->bind_param('i', $pid);
				$stmt->execute();
				$changed_row_count += $stmt->affected_rows;
			}

			$stmt->close();

			return tpl::render('redirect', array(
				'redirect_message' => '<p>'.$changed_row_count.' page(s) successfully unpublished. '.
									  'You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/pages')
				));
		}
		else
			$this->redirect(url('admin/pages'));
	}
}
