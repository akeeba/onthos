<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Issues\IssueInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var IssueInterface     $issue
 * @var ExtensionInterface $extension
 */

defined('_JEXEC') || die;
?>

<p>
	<?= Text::_('COM_ONTHOS_ISSUES_FIX_REBUILD_UPDATE_SITE') ?>
</p>

<form action="index.php?option=com_joomlaupdate&task=update.purge" method="post">
	<?= HTMLHelper::_('form.token'); ?>
	<a href="index.php?option=com_installer&view=updatesites&task=updatesites.rebuild"
	   class="btn btn-primary">
		<?= Text::_('COM_ONTHOS_ISSUES_LBL_REBUILD_UPDATE_SITE') ?>
	</a>
</form>
