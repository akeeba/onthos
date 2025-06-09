<?php
/**
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/**
 * @var  \Akeeba\Component\Onthos\Administrator\View\Item\HtmlView $this
 */

use Joomla\CMS\Language\Text;

?>

<div class="px-4 py-5 my-5 text-center">
	<span class="fa-8x mb-4 fa fa-circle-exclamation text-danger" aria-hidden="true"></span>
	<h1 class="display-5 fw-bold text-danger">
		<?= Text::_('COM_ONTHOS_ITEM_NOTSELECTED_HEAD') ?>
	</h1>
	<div class="col-lg-6 mx-auto">
		<p class="lead mb-4">
			<?= Text::_('COM_ONTHOS_ITEM_NOTSELECTED_LEAD') ?>
		</p>
	</div>
</div>
