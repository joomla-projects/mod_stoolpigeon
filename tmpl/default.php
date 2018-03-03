<?php
/**
 * default.php
 * Copyright (C) 2011-2012 www.comunidadjoomla.org. All rights reserved.
 * GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('stylesheet', 'mod_stoolpigeon/template.css', array(), true);
?>

<div class="mod-stoolpigeon<?php echo $moduleclass_sfx ?>">
<?php
echo $languages_info;
?>
</div>
