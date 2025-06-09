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

use Joomla\CMS\Language\Text;

if (!$this->item->getCanonicalUpdateServers() && !$this->item->getUpdateSites())
{
	return;
}

$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');
$unknownText = Text::_('COM_ONTHOS_ITEM_APP_UNKNOWN');
?>
<div class="<?= $this->cardWrapperClass ?>">
	<h3 class="<?= $this->cardHeaderClass ?> hasTooltip"
		title="<?= Text::_('COM_ONTHOS_ITEM_HEAD_UPDATES_TOOLTIP') ?>"
	>
		<?= Text::_('COM_ONTHOS_ITEM_HEAD_UPDATES') ?>
	</h3>

	<div class="<?= $this->cardBodyClass ?>">
		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4><?= Text::_('COM_ONTHOS_ITEM_HEAD_UPDATES_CANONICAL') ?></h4>

				<?php if ($this->item?->getCanonicalUpdateServers()): ?>
					<ul>
						<?php foreach($this->item?->getCanonicalUpdateServers() as $url): ?>
							<li>
								<?= $this->escape($url) ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<div class="alert alert-info" role="alert">
						<?= Text::_('COM_ONTHOS_ITEM_LBL_NO_CANONICAL_UPDATES') ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="col">
				<h4><?= Text::_('COM_ONTHOS_ITEM_HEAD_UPDATES_DATABASE') ?></h4>

				<?php if ($this->item?->getUpdateSites()): ?>
					<ul>
						<?php foreach($this->item?->getUpdateSites() as $updateSite): ?>
							<li class="<?= $updateSite->enabled ? '' : 'text-danger' ?>">
								<?php if ($updateSite->enabled): ?>
									<span class="fa <?= $isJoomla5Plus ? 'fa-circle-check' : 'fa-check-circle' ?> hasTooltip" aria-hidden="true"
										  title="<?= Text::_('JENABLED') ?>"
									></span>
									<span class="visually-hidden"><?= Text::_('JENABLED') ?></span>
								<?php else: ?>
									<span class="fa <?= $isJoomla5Plus ? 'fa-circle-xmark' : 'fa-times-circle' ?> hasTooltip" aria-hidden="true"
										  title="<?= Text::_('JDISABLED') ?>"
									></span>
									<span class="visually-hidden"><?= Text::_('JDISABLED') ?></span>
								<?php endif ?>
								<?= $this->escape($updateSite->location) ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<div class="alert alert-info" role="alert">
						<?= Text::_('COM_ONTHOS_ITEM_LBL_NO_DB_UPDATES') ?>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>

</div>