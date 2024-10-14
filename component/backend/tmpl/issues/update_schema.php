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
use Joomla\CMS\Router\Route;

/**
 * @var IssueInterface $issue
 * @var ExtensionInterface $extension
 */

defined('_JEXEC') || die;
?>

<p>
	<?= Text::_('COM_ONTHOS_ISSUES_FIX_UPDATE_SCHEMA') ?>
</p>

<form action="<?= Route::_('index.php?option=com_onthos&task=item.fix') ?>" method="post">
	<input type="hidden" name="id" value="<?= $extension->extension_id ?>">
	<input type="hidden" name="issue" value="<?= $issue->getSlug() ?>">
	<input type="hidden" name="redirect" value="<?= base64_encode('index.php?option=com_onthos&view=item&id=' . $extension->extension_id) ?>">
	<?= HTMLHelper::_('form.token'); ?>

	<p class="ps-3">
		<button type="submit" class="btn btn-primary">
			<?= Text::_('COM_ONTHOS_ISSUES_LBL_UPDATE_SCHEMA') ?>
		</button>

		<a href="index.php?option=com_installer&view=database&filter[search]=id:<?= $extension->extension_id ?>"
		   class="btn btn-link">
			<?= Text::_('COM_ONTHOS_ISSUES_LBL_GOTO_DATABASE_FIX') ?>
		</a>
	</p>
</form>