<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Issues\IssueInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var IssueInterface $issue
 * @var ExtensionInterface $extension
 */

?>
<?php if ($extension->isCore()): ?>
	<p>
		<?= Text::_('COM_ONTHOS_ISSUES_FIX_REINSTALL_CORE') ?>
	</p>

	<form action="index.php?option=com_joomlaupdate&task=update.purge" method="post">
		<?= HTMLHelper::_('form.token'); ?>
		<p>
			<button type="submit" class="btn btn-primary">
				<?= Text::_('COM_ONTHOS_ISSUES_FIX_JOOMLAUPDATE'); ?>
			</button>
		</p>
	</form>

<?php else: ?>
	<p>
		<?= Text::_('COM_ONTHOS_ISSUES_FIX_REINSTALL') ?>
	</p>
	<?php if ($extension->getParentPackage()?->extension_id && !$extension->issues->hasIssue('orphaned') && !$extension->issues->hasIssue('wrongparent')): ?>
		<p>
			<?= Text::_('COM_ONTHOS_ISSUES_FIX_UNINSTALL_OPTIONAL_PACKAGE', $extension->getParentPackage()?->getName() ?? '???', $extension->getParentPackage()->extension_id ?? 0) ?>
		</p>
	<?php else: ?>
		<p>
		<?= Text::_('COM_ONTHOS_ISSUES_FIX_UNINSTALL_OPTIONAL') ?>
		</p>
	<?php endif ?>
<?php endif; ?>