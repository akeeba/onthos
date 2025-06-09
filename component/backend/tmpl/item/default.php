<?php
/**
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @var  \Akeeba\Component\Onthos\Administrator\View\Item\HtmlView $this
 */

use Akeeba\Component\Onthos\Administrator\Library\Extension\Package;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');

$unknownText = Text::_('COM_ONTHOS_ITEM_APP_UNKNOWN');

?>

<?= $this->loadAnyTemplate('commontemplates/warning') ?>

<form action="index.php?option=com_onthos" method="post" id="adminForm" name="adminForm">
	<?= HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="cid[]" value="<?= $this->item->extension_id ?>">
	<input type="hidden" name="boxchecked" value="<?= $this->item->extension_id ?>">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="redirect" value="<?= base64_encode('index.php?option=com_onthos&view=item&id=' . $this->item->extension_id) ?>">
</form>

<?= $this->loadTemplate('basicinfo') ?>
<?= $this->loadTemplate('manifest') ?>
<?= $this->loadTemplate('filesdirs') ?>
<?= $this->loadTemplate('database') ?>
<?= $this->loadTemplate('updates') ?>
<?= $this->loadTemplate('issues') ?>
