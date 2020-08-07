<?php
/**
 * helper.php
 * Copyright (C) 2011-2012 www.comunidadjoomla.org. All rights reserved.
 * GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.archive.zip');

abstract class modStoolpigeonHelper
{

	public static function getLanguageInfo(&$module, &$params)
	{
		require_once(JPATH_ROOT . '/' . 'modules' . '/' . 'mod_stoolpigeon' . '/' . 'libraries' . '/' . 'pclzip.lib.php');
		require_once(JPATH_ROOT . '/' . 'modules' . '/' . 'mod_stoolpigeon' . '/' . 'libraries' . '/' . 'mod_stoolpigeon_functions.php');

		$module_id = $module->id;

		$matrix_info              = array();
		$matrix_info['module_id'] = $module_id;
		$matrix_info['module_id'];
		$matrix_info['actual_module'] = "module_" . $module_id;

		$source_language_tag = $params->get('source_language_tag', 'en-GB');
		$target_language_tag = $params->get('target_language_tag', '');

		if (isTag($source_language_tag) == false)
		{
			JFactory::getApplication()->enqueueMessage("SOURCE LANGUAGE TAG '" . $source_language_tag . "' has an invalid format. Valid formats are: xx-XX or xx-XXX", 'warning');
		}
		if (isTag($target_language_tag) == false)
		{
			JFactory::getApplication()->enqueueMessage("TARGET LANGUAGE TAG '" . $target_language_tag . "' has an invalid format. Valid formats are: xx-XX or xx-XXX", 'warning');
		}
		$client_selection = $params->get('client_selection', 'admin_selected');

		$source_installation_folder = $params->get('source_installation_folder', '_installation/language');

		$source_site_folder = $params->get('source_site_folder', 'language');

		$source_admin_folder = $params->get('source_admin_folder', 'language');

		$target_installation_folder = $params->get('target_installation_folder', '_installation/language');

		$target_site_folder = $params->get('target_site_folder', 'language');

		$target_admin_folder = $params->get('target_admin_folder', 'language');

		$mandatory_admin_ini_files = $params->get('mandatory_admin_ini_files', 'com_admin.ini,|, com_admin.sys.ini,|, com_ajax.ini,|, com_ajax.sys.ini,|, com_banners.ini,|, com_banners.sys.ini,|, com_cache.ini,|, com_cache.sys.ini,|, com_categories.ini,|, com_categories.sys.ini,|, com_checkin.ini,|, com_checkin.sys.ini,|, com_config.ini,|, com_config.sys.ini,|, com_contact.ini,|, com_contact.sys.ini,|, com_content.ini,|, com_content.sys.ini,|, com_contenthistory.ini,|, com_contenthistory.sys.ini,|, com_cpanel.ini,|, com_cpanel.sys.ini,|, com_finder.ini,|, com_finder.sys.ini,|, com_installer.ini,|, com_installer.sys.ini,|, com_joomlaupdate.ini,|, com_joomlaupdate.sys.ini,|, com_languages.ini,|, com_languages.sys.ini,|, com_login.ini,|, com_login.sys.ini,|, com_mailto.sys.ini,|, com_media.ini,|, com_media.sys.ini,|, com_menus.ini,|, com_menus.sys.ini,|, com_messages.ini,|, com_messages.sys.ini,|, com_modules.ini,|, com_modules.sys.ini,|, com_newsfeeds.ini,|, com_newsfeeds.sys.ini,|, com_plugins.ini,|, com_plugins.sys.ini,|, com_postinstall.ini,|, com_postinstall.sys.ini,|, com_redirect.ini,|, com_redirect.sys.ini,|, com_search.ini,|, com_search.sys.ini,|, com_tags.ini,|, com_tags.sys.ini,|, com_templates.ini,|, com_templates.sys.ini,|, com_users.ini,|, com_users.sys.ini,|, com_weblinks.ini,|, com_weblinks.sys.ini,|, com_wrapper.ini,|, com_wrapper.sys.ini,|, ini,|, lib_joomla.ini,|, mod_custom.ini,|, mod_custom.sys.ini,|, mod_feed.ini,|, mod_feed.sys.ini,|, mod_latest.ini,|, mod_latest.sys.ini,|, mod_logged.ini,|, mod_logged.sys.ini,|, mod_login.ini,|, mod_login.sys.ini,|, mod_menu.ini,|, mod_menu.sys.ini,|, mod_multilangstatus.ini,|, mod_multilangstatus.sys.ini,|, mod_popular.ini,|, mod_popular.sys.ini,|, mod_quickicon.ini,|, mod_quickicon.sys.ini,|, mod_stats_admin.ini,|, mod_stats_admin.sys.ini,|, mod_status.ini,|, mod_status.sys.ini,|, mod_submenu.ini,|, mod_submenu.sys.ini,|, mod_title.ini,|, mod_title.sys.ini,|, mod_toolbar.ini,|, mod_toolbar.sys.ini,|, mod_version.ini,|, mod_version.sys.ini,|, plg_authentication_cookie.ini,|, plg_authentication_cookie.sys.ini,|, plg_authentication_gmail.ini,|, plg_authentication_gmail.sys.ini,|, plg_authentication_joomla.ini,|, plg_authentication_joomla.sys.ini,|, plg_authentication_ldap.ini,|, plg_authentication_ldap.sys.ini,|, plg_captcha_recaptcha.ini,|, plg_captcha_recaptcha.sys.ini,|, plg_content_contact.ini,|, plg_content_contact.sys.ini,|, plg_content_emailcloak.ini,|, plg_content_emailcloak.sys.ini,|, plg_content_finder.ini,|, plg_content_finder.sys.ini,|, plg_content_joomla.ini,|, plg_content_joomla.sys.ini,|, plg_content_loadmodule.ini,|, plg_content_loadmodule.sys.ini,|, plg_content_pagebreak.ini,|, plg_content_pagebreak.sys.ini,|, plg_content_pagenavigation.ini,|, plg_content_pagenavigation.sys.ini,|, plg_content_vote.ini,|, plg_content_vote.sys.ini,|, plg_editors-xtd_article.ini,|, plg_editors-xtd_article.sys.ini,|, plg_editors-xtd_image.ini,|, plg_editors-xtd_image.sys.ini,|, plg_editors-xtd_pagebreak.ini,|, plg_editors-xtd_pagebreak.sys.ini,|, plg_editors-xtd_readmore.ini,|, plg_editors-xtd_readmore.sys.ini,|, plg_editors-xtd_weblink.ini,|, plg_editors-xtd_weblink.sys.ini,|, plg_editors_codemirror.ini,|, plg_editors_codemirror.sys.ini,|, plg_editors_none.ini,|, plg_editors_none.sys.ini,|, plg_editors_tinymce.ini,|, plg_editors_tinymce.sys.ini,|, plg_extension_joomla.ini,|, plg_extension_joomla.sys.ini,|, plg_finder_categories.ini,|, plg_finder_categories.sys.ini,|, plg_finder_contacts.ini,|, plg_finder_contacts.sys.ini,|, plg_finder_content.ini,|, plg_finder_content.sys.ini,|, plg_finder_newsfeeds.ini,|, plg_finder_newsfeeds.sys.ini,|, plg_finder_tags.ini,|, plg_finder_tags.sys.ini,|, plg_finder_weblinks.ini,|, plg_finder_weblinks.sys.ini,|, plg_installer_webinstaller.ini,|, plg_installer_webinstaller.sys.ini,|, plg_quickicon_extensionupdate.ini,|, plg_quickicon_extensionupdate.sys.ini,|, plg_quickicon_joomlaupdate.ini,|, plg_quickicon_joomlaupdate.sys.ini,|, plg_search_categories.ini,|, plg_search_categories.sys.ini,|, plg_search_contacts.ini,|, plg_search_contacts.sys.ini,|, plg_search_content.ini,|, plg_search_content.sys.ini,|, plg_search_newsfeeds.ini,|, plg_search_newsfeeds.sys.ini,|, plg_search_tags.ini,|, plg_search_tags.sys.ini,|, plg_search_weblinks.ini,|, plg_search_weblinks.sys.ini,|, plg_system_cache.ini,|, plg_system_cache.sys.ini,|, plg_system_debug.ini,|, plg_system_debug.sys.ini,|, plg_system_highlight.ini,|, plg_system_highlight.sys.ini,|, plg_system_languagecode.ini,|, plg_system_languagecode.sys.ini,|, plg_system_languagefilter.ini,|, plg_system_languagefilter.sys.ini,|, plg_system_log.ini,|, plg_system_log.sys.ini,|, plg_system_logout.ini,|, plg_system_logout.sys.ini,|, plg_system_p3p.ini,|, plg_system_p3p.sys.ini,|, plg_system_redirect.ini,|, plg_system_redirect.sys.ini,|, plg_system_remember.ini,|, plg_system_remember.sys.ini,|, plg_system_sef.ini,|, plg_system_sef.sys.ini,|, plg_system_weblinks.ini,|, plg_system_weblinks.sys.ini,|, plg_twofactorauth_totp.ini,|, plg_twofactorauth_totp.sys.ini,|, plg_twofactorauth_yubikey.ini,|, plg_twofactorauth_yubikey.sys.ini,|, plg_user_contactcreator.ini,|, plg_user_contactcreator.sys.ini,|, plg_user_joomla.ini,|, plg_user_joomla.sys.ini,|, plg_user_profile.ini,|, plg_user_profile.sys.ini,|, tpl_hathor.ini,|, tpl_hathor.sys.ini,|, tpl_isis.ini,|, tpl_isis.sys.ini,|, plg_editors-xtd_module.ini,|, plg_editors-xtd_module.sys.ini,|, plg_system_stats.ini,|, plg_system_stats.sys.ini,|, plg_system_updatenotification.ini,|, plg_system_updatenotification.sys.ini,|, plg_system_sessiongc.ini,|, plg_system_sessiongc.sys.ini');

		$mandatory_admin_non_ini_tagged_files = $params->get('mandatory_admin_non_ini_tagged_files', 'xml,|, localise.php');
		$mandatory_admin_non_ini_files        = $params->get('mandatory_admin_non_ini_files', 'install.xml');

		$mandatory_site_ini_files = $params->get('mandatory_site_ini_files', 'com_ajax.ini,|, com_config.ini,|, com_contact.ini,|, com_content.ini,|, com_finder.ini,|, com_mailto.ini,|, com_media.ini,|, com_messages.ini,|, com_newsfeeds.ini,|, com_search.ini,|, com_tags.ini,|, com_users.ini,|, com_weblinks.ini,|, com_wrapper.ini,|, files_joomla.sys.ini,|, finder_cli.ini,|, ini,|, lib_fof.ini,|, lib_fof.sys.ini,|, lib_idna_convert.sys.ini,|, lib_joomla.ini,|, lib_joomla.sys.ini,|, lib_phputf8.sys.ini,|, lib_phpass.sys.ini,|, lib_simplepie.sys.ini,|, mod_articles_archive.ini,|, mod_articles_archive.sys.ini,|, mod_articles_categories.ini,|, mod_articles_categories.sys.ini,|, mod_articles_category.ini,|, mod_articles_category.sys.ini,|, mod_articles_latest.ini,|, mod_articles_latest.sys.ini,|, mod_articles_news.ini,|, mod_articles_news.sys.ini,|, mod_articles_popular.ini,|, mod_articles_popular.sys.ini,|, mod_banners.ini,|, mod_banners.sys.ini,|, mod_breadcrumbs.ini,|, mod_breadcrumbs.sys.ini,|, mod_custom.ini,|, mod_custom.sys.ini,|, mod_feed.ini,|, mod_feed.sys.ini,|, mod_finder.ini,|, mod_finder.sys.ini,|, mod_footer.ini,|, mod_footer.sys.ini,|, mod_languages.ini,|, mod_languages.sys.ini,|, mod_login.ini,|, mod_login.sys.ini,|, mod_menu.ini,|, mod_menu.sys.ini,|, mod_random_image.ini,|, mod_random_image.sys.ini,|, mod_related_items.ini,|, mod_related_items.sys.ini,|, mod_search.ini,|, mod_search.sys.ini,|, mod_stats.ini,|, mod_stats.sys.ini,|, mod_syndicate.ini,|, mod_syndicate.sys.ini,|, mod_tags_popular.ini,|, mod_tags_popular.sys.ini,|, mod_tags_similar.ini,|, mod_tags_similar.sys.ini,|, mod_users_latest.ini,|, mod_users_latest.sys.ini,|, mod_weblinks.ini,|, mod_weblinks.sys.ini,|, mod_whosonline.ini,|, mod_whosonline.sys.ini,|, mod_wrapper.ini,|, mod_wrapper.sys.ini,|, tpl_beez3.ini,|, tpl_beez3.sys.ini,|, tpl_protostar.ini,|, tpl_protostar.sys.ini');

		$mandatory_site_non_ini_tagged_files = $params->get('mandatory_site_non_ini_tagged_files', 'xml,|, localise.php');
		$mandatory_site_non_ini_files        = $params->get('mandatory_site_non_ini_files', 'install.xml');

		$mandatory_installation_ini_files            = $params->get('mandatory_installation_ini_files', 'ini');
		$mandatory_installation_non_ini_tagged_files = $params->get('mandatory_installation_non_ini_tagged_files', 'xml');
		$mandatory_installation_non_ini_files        = $params->get('mandatory_installation_non_ini_files', '');

		$same_words_keys                = $params->get('same_words_keys', '');
		$matrix_info['same_words_keys'] = $same_words_keys;
		$admin_files_to_keep_in_target  = $params->get('admin_files_to_keep_in_target', '');
		$site_files_to_keep_in_target   = $params->get('site_files_to_keep_in_target', '');


		$keys_to_keep_in_target                = $params->get('keys_to_keep_in_target', 'PLG_RECAPTCHA_CUSTOM_LANG,|, PLG_RECAPTCHA_INSTRUCTIONS_VISUAL,|, PLG_RECAPTCHA_INSTRUCTIONS_AUDIO,|, PLG_RECAPTCHA_PLAY_AGAIN,|, PLG_RECAPTCHA_CANT_HEAR_THIS,|, PLG_RECAPTCHA_VISUAL_CHALLENGE,|, PLG_RECAPTCHA_AUDIO_CHALLENGE,|, PLG_RECAPTCHA_REFRESH_BTN,|, PLG_RECAPTCHA_HELP_BTN,|, PLG_RECAPTCHA_INCORRECT_TRY_AGAIN');
		$matrix_info['keys_to_keep_in_target'] = $keys_to_keep_in_target;

		$display_options         = array();
		$display_options         = $params->get('options_to_display', '');
		$version_options         = $params->get('version_options', '');
		$system_messages_options = $params->get('system_messages_to_display', '');
		$experimental_options    = $params->get('experimental_options_to_display', '');
		$experimental_extras     = $params->get('experimental_extras', '');
		$experimental_mode       = $params->get('experimental_mode', 'none');
		$are_same                = '';
		$are_same_errors         = '';
		//Works in Joomla! 1.6 and 1.5 (1.5 can to have spaces and all type of symbols between keys and a hard separator solve
		$mandatory_admin_ini_files        = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_admin_ini_files));
		$mandatory_site_ini_files         = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_site_ini_files));
		$mandatory_installation_ini_files = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_installation_ini_files));

		$mandatory_admin_ini_files        = explode(',|,', $mandatory_admin_ini_files);
		$mandatory_site_ini_files         = explode(',|,', $mandatory_site_ini_files);
		$mandatory_installation_ini_files = explode(',|,', $mandatory_installation_ini_files);

		//Works in Joomla! 1.6 and 1.5 (1.5 can to have spaces  and all type of symbols between keys and a hard separator solve
		$mandatory_admin_non_ini_files = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_admin_non_ini_files));
		$mandatory_site_non_ini_files  = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_site_non_ini_files));
		$mandatory_admin_non_ini_files = explode(',|,', $mandatory_admin_non_ini_files);
		$mandatory_site_non_ini_files  = explode(',|,', $mandatory_site_non_ini_files);

		if (!empty ($mandatory_installation_non_ini_files))
		{
			$mandatory_installation_non_ini_files = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_installation_non_ini_files));
			$mandatory_installation_non_ini_files = (array) explode(',|,', $mandatory_installation_non_ini_files);
		}
		else
		{
			$mandatory_installation_non_ini_files = array();
		}


		if (!empty ($admin_files_to_keep_in_target))
		{
			$admin_files_to_keep_in_target                = preg_replace('/,\|,\s+/', ',|,', trim($admin_files_to_keep_in_target));
			$matrix_info['admin_files_to_keep_in_target'] = explode(',|,', $admin_files_to_keep_in_target);
		}
		else
		{
			$matrix_info['admin_files_to_keep_in_target'] = array();
		}

		if (!empty ($site_files_to_keep_in_target))
		{
			$site_files_to_keep_in_target                = preg_replace('/,\|,\s+/', ',|,', trim($site_files_to_keep_in_target));
			$matrix_info['site_files_to_keep_in_target'] = explode(',|,', $site_files_to_keep_in_target);
		}
		else
		{
			$matrix_info['site_files_to_keep_in_target'] = array();
		}


		//Works in Joomla! 1.6 and 1.5 (1.5 can to have spaces  and all type of symbols between keys and a hard separator solve
		$mandatory_admin_non_ini_tagged_files        = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_admin_non_ini_tagged_files));
		$mandatory_site_non_ini_tagged_files         = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_site_non_ini_tagged_files));
		$mandatory_installation_non_ini_tagged_files = preg_replace('/,\|,\s+/', ',|,', trim($mandatory_installation_non_ini_tagged_files));

		$mandatory_admin_non_ini_tagged_files        = explode(',|,', $mandatory_admin_non_ini_tagged_files);
		$mandatory_site_non_ini_tagged_files         = explode(',|,', $mandatory_site_non_ini_tagged_files);
		$mandatory_installation_non_ini_tagged_files = explode(',|,', $mandatory_installation_non_ini_tagged_files);

		$source_a_folder                       = JPath::check(JPATH_ADMINISTRATOR . '/' . $source_admin_folder . '/' . $source_language_tag);
		$source_s_folder                       = JPath::check(JPATH_ROOT . '/' . $source_site_folder . '/' . $source_language_tag);
		$source_i_folder                       = JPath::check(JPATH_ROOT . '/' . $source_installation_folder . '/' . $source_language_tag);
		$target_a_folder                       = JPath::check(JPATH_ADMINISTRATOR . '/' . $target_admin_folder . '/' . $target_language_tag);
		$target_s_folder                       = JPath::check(JPATH_ROOT . '/' . $target_site_folder . '/' . $target_language_tag);
		$target_i_folder                       = JPath::check(JPATH_ROOT . '/' . $target_installation_folder . '/' . $target_language_tag);
		$matrix_info['config']['paths']['saf'] = $source_a_folder;
		$matrix_info['config']['paths']['ssf'] = $source_s_folder;
		$matrix_info['config']['paths']['sif'] = $source_i_folder;
		$matrix_info['config']['paths']['taf'] = $target_a_folder;
		$matrix_info['config']['paths']['tsf'] = $target_s_folder;
		$matrix_info['config']['paths']['tif'] = $target_i_folder;

		if (is_array($display_options))
		{
			$are_same_option = (in_array('are_same_option', $display_options)) ? 1 : 0;
		}
		else
		{
			$are_same_option = '0';
		}
		if (is_array($display_options))
		{
			$hide_tables = (in_array('hide_tables', $display_options)) ? 1 : 0;
		}
		else
		{
			$hide_tables = '0';
		}
		if (is_array($display_options))
		{
			$display_catched = (in_array('display_catched', $display_options)) ? 1 : 0;
		}
		else
		{
			$display_catched = '0';
		}
		if (is_array($display_options))
		{
			$display_diff = (in_array('display_diff', $display_options)) ? 1 : 0;
		}
		else
		{
			$display_diff = '0';
		}
		if (is_array($display_options))
		{
			$hide_keys_amount = (in_array('hide_keys_amount', $display_options)) ? 1 : 0;
		}
		else
		{
			$hide_keys_amount = '0';
		}
		if (is_array($display_options))
		{
			$hide_mandatory_info = (in_array('hide_mandatory_info', $display_options)) ? 1 : 0;
		}
		else
		{
			$hide_mandatory_info = '0';
		}
		if (is_array($display_options))
		{
			$hide_source_info = (in_array('hide_source_info', $display_options)) ? 1 : 0;
		}
		else
		{
			$hide_source_info = '0';
		}
		if (is_array($display_options))
		{
			$hide_target_info = (in_array('hide_target_info', $display_options)) ? 1 : 0;
		}
		else
		{
			$hide_target_info = '0';
		}
		if (is_array($version_options))
		{
			$catch_quotes = (in_array('catch_quotes', $version_options)) ? 1 : 0;
		}
		else
		{
			$catch_quotes = '0';
		}
		if (is_array($system_messages_options))
		{
			$bom_as_system_message = (in_array('bom_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$bom_as_system_message = '0';
		}
		if (is_array($system_messages_options))
		{
			$eol_as_system_message = (in_array('eol_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$eol_as_system_message = '0';
		}
		if (is_array($system_messages_options))
		{
			$extra_space_as_system_message = (in_array('extra_space_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$extra_space_as_system_message = '0';
		}
		if (is_array($system_messages_options))
		{
			$missed_quotes_as_system_message = (in_array('missed_quotes_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$missed_quotes_as_system_message = '0';
		}
		if (is_array($system_messages_options))
		{
			$bad_usage_quotes_as_system_message = (in_array('bad_usage_quotes_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$bad_usage_quotes_as_system_message = '0';
		}
		if (is_array($system_messages_options))
		{
			$missed_equal_as_system_message = (in_array('missed_equal_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$missed_equal_as_system_message = '0';
		}
		if (is_array($system_messages_options))
		{
			$missed_keys_as_system_message = (in_array('missed_keys_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$missed_keys_as_system_message = '0';
		}
		if (is_array($system_messages_options))
		{
			$changed_keys_text_as_system_message = (in_array('changed_keys_text_as_system_message', $system_messages_options)) ? 1 : 0;
		}
		else
		{
			$changed_keys_text_as_system_message = '0';
		}
		if (is_array($experimental_options))
		{
			$relative_target_line = (in_array('relative_target_line', $experimental_options)) ? 1 : 0;
		}
		else
		{
			$relative_target_line = '0';
		}
		if (isset($experimental_mode))
		{
			if ($experimental_mode == 'synchronise_target_files')
			{
				$synchronise_target_files = 1;
			}
			else
			{
				$synchronise_target_files = '0';
			}
			if ($experimental_mode == 'enable_edit_mode')
			{
				$enable_edit_mode = 1;
			}
			else
			{
				$enable_edit_mode = '0';
			}
			if ($experimental_mode == 'backup_target_files')
			{
				$backup_target_files = 1;
			}
			else
			{
				$backup_target_files = '0';
			}
			if ($experimental_mode == 'sort_target_keys')
			{
				$sort_target_keys = 1;
			}
			else
			{
				$sort_target_keys = '0';
			}
		}

		if (is_array($experimental_options))
		{
			$report_files_keys_addeds_or_deleteds = (in_array('report_files_keys_addeds_or_deleteds', $experimental_options)) ? 1 : 0;
		}
		else
		{
			$report_files_keys_addeds_or_deleteds = '0';
		}

		if (is_array($experimental_options))
		{
			$coordinated_task = (in_array('coordinated_task', $experimental_options)) ? 1 : 0;
		}
		else
		{
			$coordinated_task = '0';
		}

		if (is_array($experimental_options))
		{
			$display_package_link = (in_array('display_package_link', $experimental_options)) ? 1 : 0;
		}
		else
		{
			$display_package_link = '0';
		}

		if (is_array($experimental_options))
		{
			$allow_liar_use = (in_array('allow_liar_use', $experimental_options)) ? 1 : 0;
		}
		else
		{
			$allow_liar_use = '0';
		}
		if (is_array($experimental_extras))
		{
			$scape_html = (in_array('scape_html', $experimental_extras)) ? 1 : 0;
		}
		else
		{
			$scape_html = '0';
		}
		if (is_array($experimental_extras))
		{
			$raw_source = (in_array('raw_source', $experimental_extras)) ? 1 : 0;
		}
		else
		{
			$raw_source = '0';
		}
		if (is_array($experimental_extras))
		{
			$raw_target = (in_array('raw_target', $experimental_extras)) ? 1 : 0;
		}
		else
		{
			$raw_target = '0';
		}

		if (is_array($experimental_extras))
		{
			$raw_source_single_quotes = (in_array('raw_source_single_quotes', $experimental_extras)) ? 1 : 0;
		}
		else
		{
			$raw_source_single_quotes = '0';
		}
		if (is_array($experimental_extras))
		{
			$raw_target_single_quotes = (in_array('raw_target_single_quotes', $experimental_extras)) ? 1 : 0;
		}
		else
		{
			$raw_target_single_quotes = '0';
		}


		if (is_array($experimental_extras))
		{
			$only_special_cases = (in_array('only_special_cases', $experimental_extras)) ? 1 : 0;
		}
		else
		{
			$only_special_cases = '0';
		}

		$source_version = $params->get('source_version', 'Unknown');
		$target_version = $params->get('target_version', 'Unknown');

		$source_language_installation_folder_exist = (JFolder::exists($source_i_folder)) ? 1 : 0;
		$source_language_admin_folder_exist        = (JFolder::exists($source_a_folder)) ? 1 : 0;
		$source_language_site_folder_exist         = (JFolder::exists($source_s_folder)) ? 1 : 0;

		$target_language_installation_folder_exist = (JFolder::exists($target_i_folder)) ? 1 : 0;
		$target_language_admin_folder_exist        = (JFolder::exists($target_a_folder)) ? 1 : 0;
		$target_language_site_folder_exist         = (JFolder::exists($target_s_folder)) ? 1 : 0;

		$matrix_info['messages']['errors']['general']                                            = array();
		$matrix_info['messages']['notes']['general']                                             = array();
		$matrix_info['messages']['notes']['mandatory_source_ini_files']['admin']                 = array();
		$matrix_info['messages']['notes']['mandatory_source_ini_files']['site']                  = array();
		$matrix_info['messages']['notes']['mandatory_source_ini_files']['installation']          = array();
		$matrix_info['messages']['notes']['mandatory_target_ini_files']['admin']                 = array();
		$matrix_info['messages']['notes']['mandatory_target_ini_files']['site']                  = array();
		$matrix_info['messages']['notes']['mandatory_target_ini_files']['installation']          = array();
		$matrix_info['config']['display_options']['are_same_option']                             = $are_same_option;
		$matrix_info['config']['display_options']['hide_tables']                                 = $hide_tables;
		$matrix_info['config']['display_options']['display_catched']                             = $display_catched;
		$matrix_info['config']['display_options']['display_diff']                                = $display_diff;
		$matrix_info['config']['display_options']['hide_keys_amount']                            = $hide_keys_amount;
		$matrix_info['config']['display_options']['hide_mandatory_info']                         = $hide_mandatory_info;
		$matrix_info['config']['display_options']['hide_source_info']                            = $hide_source_info;
		$matrix_info['config']['display_options']['hide_target_info']                            = $hide_target_info;
		$matrix_info['config']['version_options']['catch_quotes']                                = $catch_quotes;
		$matrix_info['config']['version_options']['source_version']                              = $source_version;
		$matrix_info['config']['version_options']['target_version']                              = $target_version;
		$matrix_info['config']['system_messages_options']['bom_as_system_message']               = $bom_as_system_message;
		$matrix_info['config']['system_messages_options']['eol_as_system_message']               = $eol_as_system_message;
		$matrix_info['config']['system_messages_options']['extra_space_as_system_message']       = $extra_space_as_system_message;
		$matrix_info['config']['system_messages_options']['missed_quotes_as_system_message']     = $missed_quotes_as_system_message;
		$matrix_info['config']['system_messages_options']['bad_usage_quotes_as_system_message']  = $bad_usage_quotes_as_system_message;
		$matrix_info['config']['system_messages_options']['missed_equal_as_system_message']      = $missed_equal_as_system_message;
		$matrix_info['config']['system_messages_options']['missed_keys_as_system_message']       = $missed_keys_as_system_message;
		$matrix_info['config']['system_messages_options']['changed_keys_text_as_system_message'] = $changed_keys_text_as_system_message;
		$matrix_info['config']['experimental_options']['relative_target_line']                   = $relative_target_line;
		$matrix_info['config']['experimental_mode']['synchronise_target_files']                  = $synchronise_target_files;
		$matrix_info['config']['experimental_options']['report_files_keys_addeds_or_deleteds']   = $report_files_keys_addeds_or_deleteds;
		$matrix_info['config']['experimental_options']['display_package_link']                   = $display_package_link;
		$matrix_info['config']['experimental_options']['allow_liar_use']                         = $allow_liar_use;
		$matrix_info['config']['experimental_extras']['raw_source']                              = $raw_source;
		$matrix_info['config']['experimental_extras']['raw_target']                              = $raw_target;
		$matrix_info['config']['experimental_extras']['raw_source_single_quotes']                = $raw_source_single_quotes;
		$matrix_info['config']['experimental_extras']['raw_target_single_quotes']                = $raw_target_single_quotes;
		$matrix_info['config']['experimental_extras']['scape_html']                              = $scape_html;
		$matrix_info['config']['experimental_extras']['only_special_cases']                      = $only_special_cases;
		$matrix_info['config']['experimental_mode']['enable_edit_mode']                          = $enable_edit_mode;
		$matrix_info['config']['experimental_mode']['sort_target_keys']                          = $sort_target_keys;
		$matrix_info['config']['experimental_options']['coordinated_task']                       = $coordinated_task;
		$matrix_info['config']['experimental_mode']['backup_target_files']                       = $backup_target_files;
		$matrix_info['config']['source_language_tag']                                            = $source_language_tag;
		$matrix_info['config']['target_language_tag']                                            = $target_language_tag;
		$matrix_info['config']['source_admin_folder']                                            = $source_admin_folder;
		$matrix_info['config']['target_admin_folder']                                            = $target_admin_folder;
		$matrix_info['config']['source_site_folder']                                             = $source_site_folder;
		$matrix_info['config']['target_site_folder']                                             = $target_site_folder;
		$matrix_info['config']['source_installation_folder']                                     = $source_installation_folder;
		$matrix_info['config']['target_installation_folder']                                     = $target_installation_folder;
		$matrix_info['duplicated_keys']['source_admin']                                          = '0';
		$matrix_info['duplicated_keys']['target_admin']                                          = '0';
		$matrix_info['duplicated_keys']['source_site']                                           = '0';
		$matrix_info['duplicated_keys']['target_site']                                           = '0';
		$matrix_info['duplicated_keys']['source_installation']                                   = '0';
		$matrix_info['duplicated_keys']['target_installation']                                   = '0';

		if (in_array('admin_selected', $client_selection))
		{
			$matrix_info['config']['admin_selected'] = '1';
		}
		else
		{
			$matrix_info['config']['admin_selected'] = '0';
		}

		if (in_array('site_selected', $client_selection))
		{
			$matrix_info['config']['site_selected'] = '1';
		}
		else
		{
			$matrix_info['config']['site_selected'] = '0';
		}

		if (in_array('installation_selected', $client_selection))
		{
			$matrix_info['config']['installation_selected'] = '1';
		}
		else
		{
			$matrix_info['config']['installation_selected'] = '0';
		}

		//the warning 'Undefined index' issue solved when few clients are not selecteds
		$clients_availables = array('admin', 'site', 'installation');
		foreach ($clients_availables as $client_available)
		{
			if (isset($matrix_info['config'][$client_available . '_selected']))
			{
				$clients[] = $client_available;
			}
		}
		//

		foreach ($clients as $client)
		{
			$client_data = array();

			if ($client == 'admin')
			{
				//To revise for each request client
				$client_data['client_base_path']     = JPATH_ADMINISTRATOR;//Admin = JPATH_ADMINISTRATOR, Site and installation = JPATH_ROOT
				$client_data['short_text']           = 'A';//A = Administrator, S= Site, I= Installation.
				$client_data['source_client_folder'] = $matrix_info['config']['source_admin_folder'];
				$client_data['target_client_folder'] = $matrix_info['config']['target_admin_folder'];
				if (in_array('admin_selected', $client_selection))
				{
					$client_data['client_is_selected'] = '1';
				}
				else
				{
					$client_data['client_is_selected'] = '0';
				}
				$client_data['mandatory_files'] = array('admin_ini_files'            => $mandatory_admin_ini_files,
				                                        'admin_non_ini_files'        => $mandatory_admin_non_ini_files,
				                                        'admin_non_ini_tagged_files' => $mandatory_admin_non_ini_tagged_files);
			}
			elseif ($client == 'site')
			{

				$client_data['client_base_path']     = JPATH_ROOT;//Admin = JPATH_ADMINISTRATOR, Site and installation = JPATH_ROOT
				$client_data['short_text']           = 'S';//A = Administrator, S= Site, I= Installation.
				$client_data['source_client_folder'] = $matrix_info['config']['source_site_folder'];
				$client_data['target_client_folder'] = $matrix_info['config']['target_site_folder'];
				if (in_array('site_selected', $client_selection))
				{
					$client_data['client_is_selected'] = '1';
				}
				else
				{
					$client_data['client_is_selected'] = '0';
				}
				$client_data['mandatory_files'] = array('site_ini_files'            => $mandatory_site_ini_files,
				                                        'site_non_ini_files'        => $mandatory_site_non_ini_files,
				                                        'site_non_ini_tagged_files' => $mandatory_site_non_ini_tagged_files);

			}
			elseif ($client == 'installation')
			{

				$client_data['client_base_path']     = JPATH_ROOT;//Admin = JPATH_ADMINISTRATOR, Site and installation = JPATH_ROOT
				$client_data['short_text']           = 'I';//A = Administrator, S= Site, I= Installation.
				$client_data['source_client_folder'] = $matrix_info['config']['source_installation_folder'];
				$client_data['target_client_folder'] = $matrix_info['config']['target_installation_folder'];
				if (in_array('installation_selected', $client_selection))
				{
					$client_data['client_is_selected'] = '1';
				}
				else
				{
					$client_data['client_is_selected'] = '0';
				}
				$client_data['mandatory_files'] = array('installation_ini_files'            => $mandatory_installation_ini_files,
				                                        'installation_non_ini_files'        => $mandatory_installation_non_ini_files,
				                                        'installation_non_ini_tagged_files' => $mandatory_installation_non_ini_tagged_files);
			}

			determine_client_availability($matrix_info, $client, $client_data);
			if (isset($matrix_info['config']['client_selection'][$client]))
			{
				if ($matrix_info['config']['client_selection'][$client] == '1')
				{
					catch_files_by_type($matrix_info, $client, $client_data);
				}
			}
			unset($client);
		}

		if ($backup_target_files == '1')
		{

			foreach ($clients as $client)
			{
				$client_data = array();
				if (isset($matrix_info['config']['client_selection'][$client]))
				{
					if ($matrix_info['config']['client_selection'][$client] == '1')
					{
						$client_data['type']            = "backup_target_files_";
						$client_data['discart_changes'] = '0';
						$client_data['new_base_path']   = JPATH_ROOT . '/' . "tmp" . '/' . "mod_stoolpigeon_backup_target_files";
						$client_data['files_to_zip']    = '';

						backup_target_files($matrix_info, $client, $client_data);

					}
				}
				unset($client);
			}

		}

		if ($synchronise_target_files == '1')
		{

			foreach ($clients as $client)
			{
				$client_data = array();
				if (isset($matrix_info['config']['client_selection'][$client]))
				{
					if ($matrix_info['config']['client_selection'][$client] == '1'
						&& $matrix_info['duplicated_keys']['source_' . $client] == '0'
						&& $matrix_info['duplicated_keys']['target_' . $client] == '0')
					{
						$client_data['type']            = "_converted_to_";
						$client_data['discart_changes'] = '0';
						$client_data['new_base_path']   = JPATH_ROOT . '/' . "tmp" . '/' . "mod_stoolpigeon_synchronised_files";
						$client_data['files_to_zip']    = '';

						synchronise_target($matrix_info, $client, $client_data);
					}
				}
				unset($client);
			}

		}

		if ($sort_target_keys == '1')
		{
			foreach ($clients as $client)
			{
				$client_data = array();
				if (isset($matrix_info['config']['client_selection'][$client]))
				{
					if ($matrix_info['config']['client_selection'][$client] == '1'
						&& $matrix_info['duplicated_keys']['source_' . $client] == '0'
						&& $matrix_info['duplicated_keys']['target_' . $client] == '0')
					{

						$client_data['type']            = 'sort_target_keys_';
						$client_data['discart_changes'] = '0';
						$client_data['new_base_path']   = JPATH_ROOT . '/' . "tmp" . '/' . "mod_stoolpigeon_sort_target_keys";
						$client_data['files_to_zip']    = '';

						sort_keys($matrix_info, $client, $client_data);
					}
				}
				unset($client);
			}
		}

		catch_cookies($matrix_info);

		$general_errors = $matrix_info['messages']['errors']['general'];
		$general_notes  = $matrix_info['messages']['notes']['general'];

		// Showing errors by order
		if (!empty($general_errors))
		{

			sort($general_errors);

			foreach ($general_errors as $error)
			{
				JError::raiseWarning(0, JText:: _($error));
			}
		}

		if (!empty($general_notes))
		{

			sort($general_notes);

			foreach ($general_notes as $error)
			{
				JError::raiseNotice(0, JText:: _($error));
				unset($error);
			}
		}

		foreach ($clients as $client)
		{
			if (isset($matrix_info['config']['client_selection'][$client]))
			{
				if ($matrix_info['config']['experimental_extras']['raw_source'] == '1'
					&& $matrix_info['config']['client_selection'][$client] == '1')
				{
					raw_output($matrix_info, $client, $location = 'source');
				}

				if ($matrix_info['config']['experimental_extras']['raw_target'] == '1'
					&& $matrix_info['config']['client_selection'][$client] == '1')
				{
					raw_output($matrix_info, $client, $location = 'target');
				}
			}

			unset ($client);
		}


		foreach ($clients as $client)
		{
			if (isset($matrix_info['config']['client_selection'][$client]))
			{
				if ($matrix_info['config']['experimental_extras']['raw_source_single_quotes'] == '1'
					&& $matrix_info['config']['client_selection'][$client] == '1')
				{
					raw_output_single_quotes($matrix_info, $client, $location = 'source');
				}

				if ($matrix_info['config']['experimental_extras']['raw_target_single_quotes'] == '1'
					&& $matrix_info['config']['client_selection'][$client] == '1')
				{
					raw_output_single_quotes($matrix_info, $client, $location = 'target');
				}
			}

			unset ($client);
		}

		//HIDDED espagueti-boniato here. Help for sort the keys from an specific source or target language file configured as mandatory.
		//Creating an specific module or adding temporally the mod_stoolpigeon.ini file as mandatory is easy to sort all the definitions,
		// or compare in normal mode the file "en-GB.mod_stoolpigeon.ini vs es-ES.mod_stoolpigeon.ini" as with all the other ones.

		//$unsorted_keys = $matrix_info['file_existent']['site_ini_files']['target']['files_info']['mod_stoolpigeon.ini']['keys'];
		//foreach ($unsorted_keys as $key => $text)
		//{
		//$sorted_file[] = $key . ",,||,," . $text;
		//}
		//sort($sorted_file);
		//foreach($sorted_file as $line)
		//{
		//$parts = explode(',,||,,', $line);
		//echo htmlspecialchars ($parts[0]) . "=" . htmlspecialchars ($parts[1]) . "<br/>";
		//}

		//BEGIN TO ADD STUFF TO DISPLAY AT DEFAULT.PHP
		display_means_same_table($matrix_info, $means_same_table);
		display_zone_info($matrix_info, $clients_to_display);
		$display_stuffs = '';

		if (!empty($means_same_table))
		{
			foreach ($means_same_table as $row)
			{
				$display_stuffs .= $row . "\n";
				unset($row);
			}
		}

		if (!empty($clients_to_display))
		{
			foreach ($clients_to_display as $client_to_display)
			{
				foreach ($client_to_display as $row)
				{
					$display_stuffs .= $row . "\n";
					unset($row);
				}
			}
		}
		//END TO ADD STUFF TO DISPLAY AT DEFAULT.PHP

		unset($matrix_info);
		$replaced_opening_brace = preg_replace("/" . preg_quote('{') . "/", '&#123;', $display_stuffs);

		return $replaced_opening_brace;
	} //end first if
}
