<?php

# =============================================================================
# application/controllers/admin/pages/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'pages'))
			$this->redirect(SYSTEM_BASE_URL);

		$current_page = $this->request['args'] ? intval($this->request['args']) : 1;
		$start_offset = ($current_page-1) * 20;
		$page_html = '';

		$result = $this->db->query('SELECT SQL_CALC_FOUND_ROWS node.id, node.title, node.is_published,
			(COUNT(parent.id) - 1) AS depth, node.lft, node.rgt
			FROM '.DB_PREFIX.'pages AS node, '.DB_PREFIX.'pages AS parent
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			GROUP BY node.id ORDER BY node.lft ASC LIMIT '.$start_offset.',20');

		if ($result->num_rows > 0)
		{
			$depth = -1;
			$flag = false;
			$index = 0;

			while ($row = $result->fetch_assoc())
			{
				while ($row['depth'] > $depth)
				{
					$page_html .= '<ul>'."\n".'<li>';
					$flag = false;
					$depth++;
				}

				while ($row['depth'] < $depth)
				{
					$page_html .= "</li>\n"."</ul>\n";
					$depth--;
				}

				if ($flag)
				{
					$page_html .= '</li>'."\n".'<li>';
					$flag = false;
				}

				$page_html .= '
<div class="list-row row-'.($index % 2 ? 'odd' : 'even').($row['is_published'] == '0' ? ' marked-row' : '').'">
	<input id ="ch-'.$row['id'].'" type="checkbox" name="pages[]" value="'.$row['id'].'" />
	<label for="ch-'.$row['id'].'"><strong>'.u_htmlencode($row['title']).'</strong></label>
	(page:'.$row['id'].')
	<span class="actions">
		<a class="add-icon" title="Add" href="'.url('admin/pages/add:'.$row['id']).'">Add</a>
		<a class="edit-icon" title="Edit" href="'.url('admin/pages/edit:'.$row['id']).'">Edit</a>
		<a class="delete-icon" title="Delete" href="'.url('admin/pages/delete:'.$row['id']).'&amp;'.xsrf::token().'">Delete</a>
	</span>
</div>';

				$flag = true;
				++$index;
			}

			while ($depth-- > -1)
				$page_html .= "</li>\n"."</ul>\n";
		}
		elseif ($current_page !== 1)
			return $this->send_error(404);

		$result = $this->db->query('SELECT FOUND_ROWS()');
		$page_count = $result->fetch_offset();

		$pagination = pagination($current_page, $page_count, url('admin/pages:%d'));

		return tpl::render('admin_pages', array(
			'website_section' => 'Administration',
			'page_title' => 'Pages',
			'subsection' => 'pages',
			'page_html' => $page_html,
			'pagination' => $pagination
			));
	}
}
