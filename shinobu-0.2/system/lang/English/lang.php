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
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

/* ---

	= English translation

	Prefixes
	 - s_ = Language settings
	 - t_ = Page/section titles
	 - d_ = Page/section descriptions/random text
	 - e_ = Error messages
	 - m_ = System messages
	 - g_ = A variable that is used on more pages or sections (like 'username' or 'password')
	 - b_ = Button names/values
	 - f_ = Form labels

--- */

$sys_lang = array(

	// Language settings
	's_locale'		=> 'en_EN',
	's_direction'	=> 'ltr', // ltr (Left-To-Right) or rtl (Right-To-Left)
	's_encoding'	=> 'utf-8',
	's_date_format' => 'F jS, Y', // See http://nl2.php.net/date for more information
	's_time_format' => 'H:i:s', // See http://nl2.php.net/date for more information

	// Page/section titles
	't_userlist'			=> 'Userlist',
	't_register'			=> 'Register',
	't_profile'				=> '%s\'s profile', // %s is the username
	't_login'				=> 'Log in',
	't_account_info'		=> 'Account information',
	't_change_password'		=> 'Change password',
	't_contact_info'		=> 'Contact information',
	't_settings'			=> 'Settings',
	't_contact'				=> 'Contact',
	't_information'			=> 'Information',
	't_welcome_user'		=> 'Welcome %s', // %s is the username
	't_whos_online'			=> 'Who\'s online?',

	// Page/section descriptions/random text
	'd_register'			=> 'Enter a username, e-mail adress, password and awnser the anti-bot question and you can press \'Register\'. After that you can login and edit your profile.',
	'd_profile'				=> 'From this control panel you can edit your account and contact information, change your password and edit your settings.',
	'd_login'				=> 'Use your username and password to login with the following form.',
	'd_userlist'			=> 'There is a total of %d active users. Inactive users are not showed in this list.',
	'd_page_created_by'		=> 'Created by %s on %s', // 1th %s = page author, 2nd %s = creation date
	'd_page_edited_on'		=> ' &middot; Last edited on %s',
	'd_account_info'		=> 'Essential account information.',
	'd_change_password'		=> 'First enter your old password (your current password), then your new password and then confirm your new password. Your new password must have a minimum of 6 characters.',
	'd_contact_info'		=> 'Your contact information will be displayed in your public profile unlike you main e-mail adress (if you set it to hide).',
	'd_settings'			=> 'E-mail, timezone, language and date display settings.',

	'd_whos_online_1'		=> 'There is <strong>1</strong> registered user and',
	'd_whos_online_2'		=> ' <strong>1</strong> guest online',
	'd_whos_online_3'		=> 'There are <strong>%d</strong> registered users and',
	'd_whos_online_4'		=> ' <strong>%d</strong> guests online',

	// Error messages
	'e_error'					=> 'Error',
	'e_username_error_1'		=> 'You haven\'t entered a username.',
	'e_username_error_2'		=> 'Your username is too short.',
	'e_username_error_3'		=> 'Your username is too long.',
	'e_username_error_4'		=> 'This username is already taken.',
	'e_email_error_1'			=> 'You have entered an invalid e-mail address.',
	'e_email_error_2'			=> 'The e-mail address you entered is too long.',
	'e_email_error_3'			=> 'You haven\'t entered an e-mail address.',
	'e_realname_error_1'		=> 'Your real name is too long.',
	'e_desc_error_1'			=> 'Your description is too long.',
	'e_password_error_1'		=> 'You entered the wrong password.',
	'e_password_error_2'		=> 'You haven\'t entered a new password.',
	'e_password_error_3'		=> 'Your new password is too short.',
	'e_password_error_4'		=> 'You haven\'t confirmed the new password.',
	'e_password_error_5'		=> 'Confirmation password does not match new password.',
	'e_password_error_6'		=> 'You haven\'t entered a password.',
	'e_password_error_7'		=> 'Your password is too short.',
	'e_password_error_8'		=> 'You haven\'t confirmed your password.',
	'e_password_error_9'		=> 'Confirmation password does not match your password.',
	'e_anti_bot_awnser'			=> 'Please enter your answer.',
	'e_anti_bot_wrong_awnser'	=> 'Wrong answer!',
	'e_website_error_1'			=> 'You entered an invalid website adress.',
	'e_website_error_2'			=> 'The website address you entered is too long.',
	'e_msn_error_1'				=> 'You have entered an invalid msn address.',
	'e_msn_error_2'				=> 'The msn address you entered is too long.',
	'e_yahoo_error_1'			=> 'You have entered an invalid Yahoo! address.',
	'e_yahoo_error_2'			=> 'The Yahoo! address you entered is too long.',
	'e_user_does_not_exist'		=> 'A user with that username does not exist.',
	'e_inactive_account'		=> 'This account has not been activated.',
	'e_already_logged_in'		=> 'You are already logged in!',
	'e_page_not_found'			=> '404',
	'e_page_not_found_info'		=> 'The requested URL was not found on this server.',
	'e_page_no_data_error'		=> 'No data found for this page.',
	'e_profile_not_found'		=> 'Profile not found!',
	'e_profile_not_found_info'	=> 'The requested profile could not be found.',
	'e_no_users_found'			=> 'No users found.',
	'e_no_new_registrations'	=> 'This website accepts no new registrations at this moment.',

	// System messages
	'm_login_succes'			=> 'You succesfully logged in.',
	'm_logout_succes'			=> 'You succesfully logged out.',
	'm_profile_update_succes'	=> 'The profile has been succesfully changed.',
	'm_register_succes'			=> 'You can now login and configurate everything in your profile.',

	// A variable that is used on more pages or sections (like 'username' or 'password')
	'g_username'		=> 'Username',
	'g_email'			=> 'E-mail',
	'g_realname'		=> 'Real name',
	'g_description'		=> 'Description',
	'g_password'		=> 'Password',
	'g_old_password'	=> 'Old password',
	'g_new_password'	=> 'New password',
	'g_confirm'			=> 'Confirm',
	'g_website'			=> 'Website',
	'g_msn'				=> 'MSN',
	'g_yahoo'			=> 'Yahoo!',
	'g_timezone'		=> 'Timezone',
	'g_antibot'			=> 'Anti-bot',
	'g_usergroup'		=> 'Usergroup',
	'g_private'			=> '(private)',
	'g_registered_on'	=> 'Registered on',
	'g_register_date'	=> 'Register date',
	'g_title'			=> 'Title',
	'g_ascending'		=> 'Ascending',
	'g_descending'		=> 'Descending',
	'g_pages'			=> 'Pages',
	'g_previous'		=> 'Previous',
	'g_next'			=> 'Next',
	'g_home'			=> 'Home',
	'g_userlist'		=> 'Userlist',
	'g_profile'			=> 'Profile',
	'g_admin'			=> 'Administration',
	'g_logout'			=> 'Log out',
	'g_register'		=> 'Register',
	'g_login'			=> 'Log in',
	'g_guest'			=> 'Guest',

	// Form labels
	'f_email_settings'				=> 'E-mail settings',
	'f_email_settings_choice_1'		=> 'Hide your e-mail address for everyone.',
	'f_email_settings_choice_2'		=> 'Display your e-mail address to other users, but not to guests.',
	'f_email_settings_choice_3'		=> 'Display your e-mail address to everyone.',
	'f_language'					=> 'Language',
	'f_adjust_for_dst'				=> 'Adjust for DST',
	'f_dst_question'				=> 'Daylight savings is in effect (advance times by 1 hour).',
	'f_description_info'			=> 'Tell something about yourself.',
	'f_all_usergroups'				=> 'All usergroups',
	'f_sort_by'						=> 'Sort by',
	'f_sorting_order'				=> 'Sorting order',
	'f_' => '',

	// Button names/values
	'b_login'		=> 'Log in',
	'b_cancel'		=> 'Cancel',
	'b_reset'		=> 'Reset',
	'b_update'		=> 'Update',
	'b_register'	=> 'Register',
	'b_send'		=> 'Send',
	'b_search'		=> 'Search',

	// --- Misc language variables ---

	// Translations for dates
	// NOTE: This array contain te translations for date()/gmdate(). Dates are translated/replaced by strtr().
	// Since this is the english translation and date()/gmdate() outputs an english date, this array is not needed.
	/* 'strtr' => array(
		'January'		=> 'January',
		'February'		=> 'February',
		'March'			=> 'March',
		'April'			=> 'April',
		'May'			=> 'May',
		'June'			=> 'June',
		'July'			=> 'July',
		'August'		=> 'August',
		'September'		=> 'September',
		'October'		=> 'October',
		'November'		=> 'November',
		'December'		=> 'December',

		'Jan' => 'Jan',
		'Feb' => 'Feb',
		'Mar' => 'Mar',
		'Apr' => 'Apr',
		// 'May' => 'May',
		'Jun' => 'Jun',
		'Jul' => 'Jul',
		'Aug' => 'Aug',
		'Sep' => 'Sep',
		'Oct' => 'Oct',
		'Nov' => 'Nov',
		'Dec' => 'Dec',

		'Monday'		=> 'Monday',
		'Tuesday'		=> 'Tuesday',
		'Wednesday'		=> 'Wednesday',
		'Thursday'		=> 'Thursday',
		'Friday'		=> 'Friday',
		'Saterday'		=> 'Saterday',
		'Sunday'		=> 'Sunday',

		'Mon' => 'Mon',
		'Tue' => 'Tue',
		'Wed' => 'Wed',
		'Thu' => 'Thu',
		'Fri' => 'Fri',
		'Sat' => 'Sat',
		'Sun' => 'Sun',

		'st' => 'st', // 1st
		'nd' => 'nd', // 2nd
		'rd' => 'rd', // 3rd
		'th' => 'th', // 4th, 5th, 6th and so on
		), */

	// Timezones
	'timezones' => array(
		'-12'		=> '(UTC-12:00) International Date Line West',
		'-11'		=> '(UTC-11:00) Niue, Samoa',
		'-10'		=> '(UTC-10:00) Hawaii-Aleutian, Cook Island',
		'-9.5'		=> '(UTC-09:30) Marquesas Islands',
		'-9'		=> '(UTC-09:00) Alaska, Gambier Island',
		'-8'		=> '(UTC-08:00) Pacific',
		'-7'		=> '(UTC-07:00) Mountain',
		'-6'		=> '(UTC-06:00) Central',
		'-5'		=> '(UTC-05:00) Eastern',
		'-4'		=> '(UTC-04:00) Atlantic',
		'-3.5'		=> '(UTC-03:30) Newfoundland',
		'-3'		=> '(UTC-03:00) Amazon, Central Greenland',
		'-2'		=> '(UTC-02:00) Mid-Atlantic',
		'-1'		=> '(UTC-01:00) Azores, Cape Verde, Eastern Greenland',
		'0'			=> '(UTC) Western European, Greenwich',
		'1'			=> '(UTC+01:00) Central European, West African',
		'2'			=> '(UTC+02:00) Eastern European, Central African',
		'3'			=> '(UTC+03:00) Moscow, Eastern African',
		'3.5'		=> '(UTC+03:30) Iran',
		'4'			=> '(UTC+04:00) Gulf, Samara',
		'4.5'		=> '(UTC+04:30) Afghanistan',
		'5'			=> '(UTC+05:00) Pakistan, Yekaterinburg',
		'5.5'		=> '(UTC+05:30) India, Sri Lanka',
		'5.75'		=> '(UTC+05:45) Nepal',
		'6'			=> '(UTC+06:00) Bangladesh, Bhutan, Novosibirsk',
		'6.5'		=> '(UTC+06:30) Cocos Islands, Myanmar',
		'7'			=> '(UTC+07:00) Indochina, Krasnoyarsk',
		'8'			=> '(UTC+08:00) Great China, Australian Western, Irkutsk',
		'8.75'		=> '(UTC+08:45) Southeastern Western Australia',
		'9'			=> '(UTC+09:00) Japan, Korea, Chita',
		'9.5'		=> '(UTC+09:30) Australian Central',
		'10' 		=> '(UTC+10:00) Australian Eastern, Vladivostok',
		'10.5'		=> '(UTC+10:30) Lord Howe',
		'11'		=> '(UTC+11:00) Solomon Island, Magadan',
		'11.5'		=> '(UTC+11:30) Norfolk Island',
		'12'		=> '(UTC+12:00) New Zealand, Fiji, Kamchatka',
		'12.75'		=> '(UTC+12:45) Chatham Islands',
		'13'		=> '(UTC+13:00) Tonga, Phoenix Islands',
		'14'		=> '(UTC+14:00) Line Islands',
		),
	);

?>
