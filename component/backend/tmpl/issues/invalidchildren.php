<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Issues\IssueInterface;
use Joomla\CMS\Language\Text;

/**
 * @var IssueInterface $issue
 * @var ExtensionInterface $extension
 */

?>
<p>
	<?= Text::_('COM_ONTHOS_ISSUES_FIX_INVALIDCHILDREN_REINSTALL') ?>
</p>
<p class="text-muted">
	<?= Text::_('COM_ONTHOS_ISSUES_FIX_INVALIDCHILDREN_REINSTALL_WHY') ?>
</p>
<p>
	<?= Text::_('COM_ONTHOS_ISSUES_FIX_INVALIDCHILDREN_REMOVESUBS') ?>
</p>
<p class="text-muted">
	<?= Text::_('COM_ONTHOS_ISSUES_FIX_INVALIDCHILDREN_REMOVESUBS_WHY') ?>
</p>
