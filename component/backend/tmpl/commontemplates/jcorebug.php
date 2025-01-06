<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Language\Text;
?>
<div class="alert alert-warning">
	<h4 class="alert-heading">
		<span class="fa fa-exclamation-triangle fa-fw me-2" aria-hidden="true"></span>
		<?= Text::_('COM_ONTHOS_COMMON_UNRELIABLE_CORE_HEAD') ?>
	</h4>
	<p>
		<?= Text::_('COM_ONTHOS_COMMON_UNRELIABLE_CORE_BODY') ?>
	</p>
</div>
