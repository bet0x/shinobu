<?php

/* ---

	Copyright (C) 2008 Frank Smit
	http://code.google.com/p/shinobu/

	This file is part of Shinobu.

	Shinobu is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Shinobu is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

(!defined('SHINOBU_ADMIN')) ? exit : NULL;

if ($sys_user['p_manage_nav'] == 0)
{
	// Set page title
	$sys_tpl->assign('page_title', $sys_lang['e_error'].' - '.$sys_config['website_title'].' Admin');

	?>

<h2><span><?php echo $sys_lang['e_error']; ?></span></h2>

<p>You have no permission to access this page</p>

	<?php
}

// Edit navigation item
else if (isset($sys_request[2]) && $sys_request[2] == 'edit')
{
	$sys_request[3] = isset($sys_request[3]) && !empty($sys_request[3]) ? intval($sys_request[3]) : 0;
	$result = $sys_db->query('SELECT i.* FROM '.DB_PREFIX.'navigation AS i WHERE i.id='.$sys_request[3].' LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

	// Update navigation item
	if ($sys_db->num_rows($result) > 0)
	{
		$navigation_item = $sys_db->fetch_assoc($result);

		if (isset($_POST['frm-submit']) && check_token())
		{
			$form = array_map('system_trim', $_POST['form']);
			$errors = false;

			$form['name'] = utf8_htmlencode($form['name']);
			$form['url'] = utf8_htmlencode($form['url']);
			$form['position'] = intval($form['position']);

			// Check name
			if (empty($form['name']))
				$errors['name'] = 'You must enter a name.';
			else if (utf8_strlen($form['name']) > 50)
				$errors['name'] = 'The name is too long.';

			// Check url
			if (empty($form['url']))
				$errors['url'] = 'You must enter a url.';
			else if (utf8_strlen($form['url']) > 255)
				$errors['url'] = 'The url is too long.';

			$form['visibility'] = $form['visibility'] == 1 ? 1 : 0;

			// Check/filter position
			if (empty($form['position']))
				$form['position'] = 0;
			else if ($form['position'] < 0 || $form['position'] > 100)
				$form['position'] = 0;

			if ($errors === false)
			{
				$sys_db->query('UPDATE '.DB_PREFIX.'navigation SET name=\''.$sys_db->escape($form['name']).'\', url=\''.$sys_db->escape($form['url']).'\', visibility='.$form['visibility'].', position='.intval($form['position']).' WHERE id='.$sys_request[3]) or error($sys_db->error(), __FILE__, __LINE__);
				generate_navigation(true);
				header('location: '.ADMIN_URL.URI_PREFIX.'navigation'.URI_SUFFIX.'&edited'); exit;
			}
		}

		// Set page title
		$sys_tpl->assign('page_title', 'Edit Navigation Item - '.$sys_config['website_title'].' Admin');

		?>

<h2>Edit Navigation Item: <?php echo $navigation_item['name']; ?></h2>

<p>If you would like to make a navigation item that links to an external website - you just have the enter to absolute/full url (something like <strong>http://www.example.com/</strong>). A relative url will link to an internal page. A link to page one will look like this: <strong>p/1.html</strong>.</p>

<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'navigation/edit/'.$sys_request[3].URI_SUFFIX; ?>">
	<div>
		<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
	</div>

	<ul class="frm-vc">
		<li class="frm-hr">&nbsp;</li>

		<li class="frm-block<?php echo isset($errors['name']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-0">Name:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[name]" id="fld-0" maxlength="20" value="<?php echo $navigation_item['name']; ?>" /></div>
			<?php echo isset($errors['name']) ? '<span class="fld-error-message">'.$errors['groupname'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['url']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-1">Url:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[url]" id="fld-1" maxlength="100" value="<?php echo $navigation_item['url']; ?>" /></div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label>Visibility:</label></div>
			<div class="fld-text">
				<div><label for="fld-2"><input type="radio" id="fld-2" name="form[visibility]" value="1" <?php echo $navigation_item['visibility'] == 1 ? 'checked="checked"' : NULL; ?> /> Visible</label></div>
				<div><label for="fld-3"><input type="radio" id="fld-3" name="form[visibility]" value="0" <?php echo $navigation_item['visibility'] == 0 ? 'checked="checked"' : NULL; ?> /> Hidden</label></div>
			</div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label for="fld-4">Position:</label></div>
			<div class="fld-input">
				<select name="form[position]" id="fld-4">
					<option value="<?php echo $navigation_item['position']; ?>"><?php echo $navigation_item['position']; ?></option>
					<option value="0">0</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
					<option value="24">24</option>
					<option value="25">25</option>
					<option value="26">26</option>
					<option value="27">27</option>
					<option value="28">28</option>
					<option value="29">29</option>
					<option value="30">30</option>
					<option value="31">31</option>
					<option value="32">32</option>
					<option value="33">33</option>
					<option value="34">34</option>
					<option value="35">35</option>
					<option value="36">36</option>
					<option value="37">37</option>
					<option value="38">38</option>
					<option value="39">39</option>
					<option value="40">40</option>
					<option value="41">41</option>
					<option value="42">42</option>
					<option value="43">43</option>
					<option value="44">44</option>
					<option value="45">45</option>
					<option value="46">46</option>
					<option value="47">47</option>
					<option value="48">48</option>
					<option value="49">49</option>
					<option value="50">50</option>
					<option value="51">51</option>
					<option value="52">52</option>
					<option value="53">53</option>
					<option value="54">54</option>
					<option value="55">55</option>
					<option value="56">56</option>
					<option value="57">57</option>
					<option value="58">58</option>
					<option value="59">59</option>
					<option value="60">60</option>
					<option value="61">61</option>
					<option value="62">62</option>
					<option value="63">63</option>
					<option value="64">64</option>
					<option value="65">65</option>
					<option value="66">66</option>
					<option value="67">67</option>
					<option value="68">68</option>
					<option value="69">69</option>
					<option value="70">70</option>
					<option value="71">71</option>
					<option value="72">72</option>
					<option value="73">73</option>
					<option value="74">74</option>
					<option value="75">75</option>
					<option value="76">76</option>
					<option value="77">77</option>
					<option value="78">78</option>
					<option value="79">79</option>
					<option value="80">80</option>
					<option value="81">81</option>
					<option value="82">82</option>
					<option value="83">83</option>
					<option value="84">84</option>
					<option value="85">85</option>
					<option value="86">86</option>
					<option value="87">87</option>
					<option value="88">88</option>
					<option value="89">89</option>
					<option value="90">90</option>
					<option value="91">91</option>
					<option value="92">92</option>
					<option value="93">93</option>
					<option value="94">94</option>
					<option value="95">95</option>
					<option value="96">96</option>
					<option value="97">97</option>
					<option value="98">98</option>
					<option value="99">99</option>
					<option value="100">100</option>
				</select>
			</div>
		</li>

		<li class="frm-block frm-buttons">
			<div class="fld-label">&nbsp;</div>
			<div class="fld-input">
				<input type="submit" value="Update Item" name="frm-submit" />
				<input type="button" onclick="window.location='<?php echo ADMIN_URL.URI_PREFIX.'navigation'.URI_SUFFIX; ?>'" value="Cancel" name="frm-cancel" />
			</div>
		</li>
	</ul>
</form>

		<?php
	}
	else
		send_404($sys_lang['e_error'], '<p>The navigation item does not exist.</p>', false);
}

// View navigation items
else
{
	// Delete navigation item
	if (isset($_GET['delete']) && check_token(true))
	{
		$id = intval($_GET['delete']);

		$result = $sys_db->query('SELECT n.id FROM '.DB_PREFIX.'navigation AS n WHERE id='.$id) or error($sys_db->error(), __FILE__, __LINE__);

		if ($sys_db->num_rows($result) > 0)
		{
			$sys_db->query('DELETE FROM '.DB_PREFIX.'navigation WHERE id='.$id) or error($sys_db->error(), __FILE__, __LINE__);
			generate_navigation(true);
			header('location: '.ADMIN_URL.URI_PREFIX.'navigation'.URI_SUFFIX.'&deleted'); exit;
		}
		else
		{
			header('location: '.ADMIN_URL.URI_PREFIX.'navigation'.URI_SUFFIX.'&delete_error'); exit;
		}
	}

	// Add new navigation item
	if (isset($_POST['frm-submit']) && isset($_GET['add']) && check_token())
	{
		// Set vars
		$form = array_map('system_trim', $_POST['form']);
		$errors = false;

		$form['name'] = utf8_htmlencode($form['name']);
		$form['url'] = utf8_htmlencode($form['url']);
		$form['position'] = intval($form['position']);

		// Check name
		if (empty($form['name']))
			$errors['name'] = 'You must enter a name.';
		else if (utf8_strlen($form['name']) > 50)
			$errors['name'] = 'The name is too long.';

		// Check url
		if (empty($form['url']))
			$errors['url'] = 'You must enter a url.';
		else if (utf8_strlen($form['url']) > 255)
			$errors['url'] = 'The url is too long.';

		// Check/filter position
		if (empty($form['position']))
			$form['position'] = 0;
		else if ($form['position'] < 0 || $form['position'] > 100)
			$form['position'] = 0;

		if ($errors === false)
		{
			$sys_db->query('INSERT INTO '.DB_PREFIX.'navigation (name, url, position) VALUES(\''.$sys_db->escape($form['name']).'\', \''.$sys_db->escape($form['url']).'\', '.intval($form['position']).')') or error($sys_db->error(), __FILE__, __LINE__);
			generate_navigation(true);
			header('location: '.ADMIN_URL.URI_PREFIX.'navigation'.URI_SUFFIX.'&added'); exit;
		}
	}

	if (isset($_GET['added']))
		$sys_tpl->add('main_content', '<div class="success">Navigation item succesfully added.</div>');
	else if (isset($_GET['edited']))
		$sys_tpl->add('main_content', '<div class="success">Navigation item succesfully edited.</div>');
	else if (isset($_GET['deleted']))
		$sys_tpl->add('main_content', '<div class="success">Navigation item succesfully deleted.</div>');
	else if (isset($_GET['delete_error']))
		$sys_tpl->add('main_content', '<div class="warning">The navigation item you tried to delete does not exist.</div>');

	// Set page title
	$sys_tpl->assign('page_title', 'Manage Navigation - '.$sys_config['website_title'].' Admin');

	?>

<h2>Manage Navigation</h2>

<p>Edit or delete navigation items.</p>

<table id="navigation-list">
	<thead>
		<tr>
			<th class="td-name">Name</th>
			<th class="td-url">Url</th>
			<th class="td-position">Visibility</th>
			<th class="td-position">Position</th>
			<th class="td-actions">Actions</th>
		</tr>
	</thead>
	<tbody>

	<?php

	$result = $sys_db->query('SELECT m.* FROM '.DB_PREFIX.'navigation AS m ORDER BY m.position ASC') or error($sys_db->error(), __FILE__, __LINE__);

	if ($sys_db->num_rows($result) > 0)
	{
		while ($row = $sys_db->fetch_assoc($result))
		{
			?>

		<tr>
			<td class="td-name"><?php echo $row['name']; ?></td>
			<td class="td-url"><?php echo $row['url']; ?></td>
			<td class="td-visibility"><?php echo $row['visibility'] == 1 ? 'Visible' : 'Hidden'; ?></td>
			<td class="td-position"><?php echo $row['position']; ?></td>
			<td class="td-actions"><a href="<?php echo ADMIN_URL.URI_PREFIX.'navigation/edit/'.$row['id'].URI_SUFFIX; ?>">Edit</a> - <a class="confirm" href="<?php echo ADMIN_URL.'/'.URI_PREFIX.'navigation'.URI_SUFFIX.'&amp;delete='.$row['id'].'&amp;token='.SYS_TOKEN; ?>">Delete</a></td>
		</tr>

			<?php
		}
	}
	else
	{
			?>

		<tr>
			<td colspan="4">There are no items.</td>
		</tr>

			<?php
	}

	?>

	</tbody>
</table>

<h2>Add new navigation item</h2>

<p>If you would like to make a navigation item that links to an external website - you just have the enter to absolute/full url (something like <strong>http://www.example.com/</strong>). A relative url will link to an internal page. A link to page one will look like this: <strong>p/1.html</strong>.</p>

	<?php

	if (isset($errors) && count($errors) > 0)
	{
		echo '<ul>';
		foreach ($errors as $error)
			echo '<li>'.$error.'</li>';
		echo '</ul>';
	}

	?>

<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'navigation'.URI_SUFFIX.'&amp;add'; ?>">

	<div>
		<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
	</div>

	<ul id="add-usergroup" class="frm-hc hc-box hc-inline-box">
		<li class="frm-block<?php echo isset($errors['name']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-0">Name:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[name]" id="fld-0" maxlength="50" /></div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label for="fld-1">Url:</label></div>
			<div class="fld-input">
				<input class="text" type="text" name="form[url]" id="fld-1" maxlength="255" />
			</div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label for="fld-2">Position:</label></div>
			<div class="fld-input">
				<select name="form[position]" id="fld-2">
					<option value="0">0</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
					<option value="24">24</option>
					<option value="25">25</option>
					<option value="26">26</option>
					<option value="27">27</option>
					<option value="28">28</option>
					<option value="29">29</option>
					<option value="30">30</option>
					<option value="31">31</option>
					<option value="32">32</option>
					<option value="33">33</option>
					<option value="34">34</option>
					<option value="35">35</option>
					<option value="36">36</option>
					<option value="37">37</option>
					<option value="38">38</option>
					<option value="39">39</option>
					<option value="40">40</option>
					<option value="41">41</option>
					<option value="42">42</option>
					<option value="43">43</option>
					<option value="44">44</option>
					<option value="45">45</option>
					<option value="46">46</option>
					<option value="47">47</option>
					<option value="48">48</option>
					<option value="49">49</option>
					<option value="50">50</option>
					<option value="51">51</option>
					<option value="52">52</option>
					<option value="53">53</option>
					<option value="54">54</option>
					<option value="55">55</option>
					<option value="56">56</option>
					<option value="57">57</option>
					<option value="58">58</option>
					<option value="59">59</option>
					<option value="60">60</option>
					<option value="61">61</option>
					<option value="62">62</option>
					<option value="63">63</option>
					<option value="64">64</option>
					<option value="65">65</option>
					<option value="66">66</option>
					<option value="67">67</option>
					<option value="68">68</option>
					<option value="69">69</option>
					<option value="70">70</option>
					<option value="71">71</option>
					<option value="72">72</option>
					<option value="73">73</option>
					<option value="74">74</option>
					<option value="75">75</option>
					<option value="76">76</option>
					<option value="77">77</option>
					<option value="78">78</option>
					<option value="79">79</option>
					<option value="80">80</option>
					<option value="81">81</option>
					<option value="82">82</option>
					<option value="83">83</option>
					<option value="84">84</option>
					<option value="85">85</option>
					<option value="86">86</option>
					<option value="87">87</option>
					<option value="88">88</option>
					<option value="89">89</option>
					<option value="90">90</option>
					<option value="91">91</option>
					<option value="92">92</option>
					<option value="93">93</option>
					<option value="94">94</option>
					<option value="95">95</option>
					<option value="96">96</option>
					<option value="97">97</option>
					<option value="98">98</option>
					<option value="99">99</option>
					<option value="100">100</option>
				</select>
			</div>
		</li>

		<li class="frm-block frm-buttons">
			<div class="fld-input">
				<input type="submit" value="Add Item" name="frm-submit" />
			</div>
		</li>
	</ul>
</form>

<?php } ?>
