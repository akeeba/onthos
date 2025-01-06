<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

if (ComponentHelper::getComponent('com_onthos')->params->get('show_warning', 1) == 0)
{
	return;
}
?>

<div class="alert alert-info" role="alert">
	<h3 class="alert-heading">
		<span class="fa fa-fw fa-bolt" aria-hidden="true"></span>
		<?= Text::_('COM_ONTHOS_COMMON_GLOBALWARNING_HEAD') ?>
	</h3>
	<p>
		<?= Text::_('COM_ONTHOS_COMMON_GLOBALWARNING_BODY') ?>
	</p>
	<p class="text-muted small">
		<?= Text::_('COM_ONTHOS_COMMON_GLOBALWARNING_FOOTER') ?>
	</p>
</div>