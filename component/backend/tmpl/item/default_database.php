<?php
/**
 * @package   onthos
 * @copyright Copyright (c) 2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @var  \Akeeba\Component\Onthos\Administrator\View\Item\HtmlView $this
 */

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');
$unknownText = Text::_('COM_ONTHOS_ITEM_APP_UNKNOWN');

?>
<div class="<?= $this->cardWrapperClass ?>">

	<h3 class="<?= $this->cardHeaderClass ?> hasTooltip"
		title="<?= Text::_('COM_ONTHOS_ITEM_HEAD_DATABASE_TOOLTIP') ?>"
	>
		<?= Text::_('COM_ONTHOS_ITEM_HEAD_DATABASE') ?>
	</h3>

	<div class="<?= $this->cardBodyClass ?>">
		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<?php if ($this->item?->getTables()): ?>
					<ul class="list-unstyled">
						<?php foreach($this->item?->getTables() as $table): ?>
							<?php $exists = in_array($table, $this->existingTables) ?>
							<li>
								<span class="font-monospace">
									<?php if ($exists): ?>
										<span class="fa <?= $isJoomla5Plus ? 'fa-circle-check' : 'fa-check-circle' ?>" aria-hidden="true"></span>
									<?php else: ?>
										<span class="fa <?= $isJoomla5Plus ? 'fa-circle-xmark' : 'fa-times-circle' ?>  text-danger" aria-hidden="true"></span>
									<?php endif ?>
									<?= $this->escape($table) ?>
								</span>
								<span class="ms-3 small text-muted font-monospace d-block d-md-none">
									<?= $this->escape(Factory::getContainer()->get(DatabaseInterface::class)->replacePrefix($table)) ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<span class="small text-muted">
						<?= Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
					</span>
				<?php endif ?>
			</div>
			<div class="col d-none d-md-block">
				<?php if ($this->item?->getTables()): ?>
					<ul class="list-unstyled">
						<?php foreach($this->item?->getTables() as $table): ?>
							<li>
								<span class="text-muted font-monospace">
									<?= $this->escape(Factory::getContainer()->get(DatabaseInterface::class)->replacePrefix($table)) ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>