<?php
/**
 * mod_stoolpigeon.php
 * Copyright (C) 2011-2012 www.comunidadjoomla.org. All rights reserved.
 * GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/helper.php';

$languages_info = modStoolpigeonHelper::getLanguageInfo($module, $params);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_stoolpigeon', $params->get('layout', 'default'));
