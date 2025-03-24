<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Component\Onthos\Administrator\Helper\AdoptionHelper;
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

$canonicalPackage = AdoptionHelper::whichPackage($extension->extension_id);
?>

<p>
	<?= Text::_('COM_ONTHOS_ISSUES_FIX_DISCOVERED') ?>
</p>

<form action="<?= Route::_('index.php?option=com_onthos&task=items.forced') ?>" method="post">
	<input type="hidden" name="cid[]" value="<?= $extension->extension_id ?>">
	<input type="hidden" name="redirect" value="<?= base64_encode('index.php?option=com_onthos&view=item&id=' . $extension->extension_id) ?>">
	<?= HTMLHelper::_('form.token'); ?>

	<p class="ps-3">
		<a href="index.php?option=com_installer&view=discover&filter[search]=id:<?= $extension->extension_id ?>"
		   class="btn btn-primary">
			<?= Text::_('COM_ONTHOS_ISSUES_LBL_GOTO_DISCOVER') ?>
		</a>

		<button type="submit" class="btn btn-danger">
			<?= Text::_('COM_ONTHOS_ISSUES_LBL_FORCEUNINSTALL') ?>
		</button>
	</p>
</form>
