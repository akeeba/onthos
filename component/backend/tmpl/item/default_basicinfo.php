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

use Akeeba\Component\Onthos\Administrator\Library\Extension\Package;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');
$unknownText = Text::_('COM_ONTHOS_ITEM_APP_UNKNOWN');

$minRows     = 2;
$isPlugin    = $this->item?->type === 'plugin';
$hasClientId = !in_array($this->item?->type, ['component', 'file', 'files', 'library', 'package', 'plugin']);

if ($isPlugin) $minRows++;
if ($hasClientId) $minRows++;

?>
<div class="<?= $this->cardWrapperClass ?>">
	<h3 class="<?= $this->cardHeaderClass ?>">
		<?= Text::_('COM_ONTHOS_ITEM_HEAD_BASIC') ?>
	</h3>

	<div class="<?= $this->cardBodyClass ?>">
		<h4 class="h2 text-tertiary">
			<?= $this->escape($this->item?->getName() ?? strtoupper($this->item?->name ?? 'UNKNOWN')) ?>
		</h4>

		<?php if ($this->item->isCore()): ?>
			<?= $this->loadAnyTemplate('commontemplates/jcorebug') ?>
		<?php endif ?>

		<div class="row row-cols-1 row-cols-sm-<?= intval(ceil($minRows/2)) ?> row-cols-md-<?= $minRows ?>">
			<div class="col">
				<h4>
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_TYPE') ?>
				</h4>
				<p>
					<?= Text::_('COM_INSTALLER_TYPE_' . ($this->item?->type ?? 'NONAPPLICABLE')) ?>
				</p>
			</div>
			<div class="col">
				<h4>
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_ELEMENT') ?>
				</h4>
				<p class="font-monospace">
					<?= $this->escape($this->item?->element ?? $unknownText) ?>
				</p>
			</div>
			<?php if ($isPlugin): ?>
				<div class="col">
					<h4>
						<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_FOLDER') ?>
					</h4>
					<p>
						<?= $this->escape($this->item?->folder ?? $unknownText) ?>
					</p>
				</div>
			<?php endif; ?>
			<?php if ($hasClientId): ?>
				<div class="col">
					<h4>
						<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_APPLICATION') ?>
					</h4>
					<?php if ($this->item?->client_id === 1): ?>
						<p>
							<?= Text::_('JADMINISTRATOR') ?>
						</p>
					<?php elseif ($this->item?->client_id === 0): ?>
						<p>
							<?= Text::_('JSITE') ?>
						</p>
					<?php elseif ($this->item?->client_id === 3): ?>
						<p>
							<?= Text::_('JAPI') ?>
						</p>
					<?php elseif (empty($this->item?->client_id ?? null)): ?>
						<p>
							<?= $unknownText ?>
						</p>
					<?php else: ?>

						#<?= $this->escape($this->item?->client_id ?? $unknownText) ?>
					<?php endif ?>
				</div>
			<?php endif ?>
		</div>

		<div class="row row-cols-1 row-cols-md-1">
			<div class="col">
				<?php if (!$this->item instanceof Package): ?>
					<h4>
						<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_PACKAGELINK') ?>
					</h4>
					<?php if ($this->item->isCore()): ?>
						<p class="text-info hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_LBL_CORE_TOOLTIP') ?>">
							<span class="fa fab fa-joomla" aria-hidden="true"></span>
							<strong>
								<?= Text::_('COM_ONTHOS_ITEM_LBL_CORE') ?>
							</strong>
						</p>
					<?php elseif ($this->item->getParentPackage()?->extension_id): ?>
						<p class="text-success hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_LBL_LINKED_TOOLTIP') ?>">
							<span class="fa fa-link" aria-hidden="true"></span>
							<a href="<?= Route::_('index.php?option=com_onthos&view=item&id=' . $this->item->getParentPackage()->extension_id) ?>"
							   class="link-info"
							>
								<?= $this->escape($this->item->getParentPackage()->getName()) ?>
							</a>
							<span class="small muted">
								(#<?= $this->escape($this->item->getParentPackage()->extension_id) ?>)
								–
								<a href="<?= Route::_('index.php?option=com_onthos&view=items&filter[package_id]=' . $this->item->getParentPackage()->extension_id) ?>">
									<?= Text::_('COM_ONTHOS_ITEM_LBL_LIST_ALL_IN_PACKAGE') ?>
								</a>
							</span>
						</p>
					<?php else: ?>
						<p class="text-muted hasTooltip">
							<span class="fa fa-link-slash" aria-hidden="true"></span>
							<?= Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
						</p>
					<?php endif; ?>
				<?php else: ?>
					<h4>
						<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_PACKAGECONTENTS') ?>
					</h4>
					<?php $subExtensions = $this->item->getSubextensionsWithMeta(); ?>
					<?php if (empty($subExtensions)): ?>
						<div class="alert alert-warning" role="alert">
							<span class="fa fa-exclamation-triangle" aria-hidden="true"></span>
							<?= Text::_('COM_ONTHOS_ITEM_LBL_NO_PACKAGE_CONTENTS') ?>
						</div>
					<?php else: ?>
						<ul class="list-unstyled">
							<?php foreach ($subExtensions as $subExtension): ?>
								<li class="mb-1">
									<?php if ($subExtension->installed): ?>
										<span class="fa fa-check-circle fa-fw text-success hasTooltip"
											  title="<?= Text::_('COM_ONTHOS_ITEMS_LBL_INSTALLED') ?>"
											  aria-hidden="true"></span>
										<span class="visually-hidden">
											<?= Text::_('COM_ONTHOS_ITEMS_LBL_INSTALLED') ?>:
										</span>
									<?php else: ?>
										<span class="fa <?= $isJoomla5Plus ? 'fa-circle-xmark' : 'fa-times-circle' ?> fa-fw text-danger hasTooltip"
											  title="<?= Text::_('COM_ONTHOS_ITEMS_LBL_NOTINSTALLED') ?>"
											  aria-hidden="true"></span>
										<span class="visually-hidden">
											<?= Text::_('COM_ONTHOS_ITEMS_LBL_NOTINSTALLED') ?>:
										</span>
									<?php endif ?>

									<?php if ($subExtension->client_id !== null && in_array($subExtension->client_id, [0, 1, 2])): ?>
										<?= Text::_('J' . ([0 => 'site', 1 => 'administrator', 2 => 'api'][$subExtension->client_id])) ?>
									<?php endif; ?>

									<?php if ($subExtension->folder !== null): ?>
										<span class="font-monospace"><?= $this->escape($subExtension->folder) ?></span>
									<?php endif; ?>

									<?= Text::_('COM_INSTALLER_TYPE_' . $subExtension->type) ?>:

									<?php if ($subExtension->installed): ?><a href="<?= Route::_('index.php?option=com_onthos&view=item&id=' . $subExtension->extension->extension_id) ?>"
																			  class="link-info"
									><?php endif; ?>

										<?php if ($subExtension->installed): ?>
											<em><?= $subExtension->extension->getName() ?></em>
										<?php else: ?>
											<span class="font-monospace">
												<?= $this->escape($subExtension->element) ?>
											</span>
										<?php endif ?>

										<?php if ($subExtension->installed): ?></a><?php endif; ?>

									<small class="text-muted">(#<?= $subExtension->extension->extension_id ?>)</small>

								</li>
							<?php endforeach ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="row row-cols-1 row-cols-md-3">
			<div class="col">
				<h4><?= Text::_('COM_ONTHOS_ITEMS_FIELD_STATE') ?></h4>
				<?php if ($this->item?->enabled): ?>
					<p class="text-success">
						<span class="fa <?= $isJoomla5Plus ? 'fa-circle-check' : 'fa-check-circle' ?> " aria-hidden="true"></span>
						<strong><?= Text::_('JENABLED') ?></strong>
					</p>
				<?php else: ?>
					<p class="text-danger">
						<span class="fa <?= $isJoomla5Plus ? 'fa-circle-xmark' : 'fa-times-circle' ?>" aria-hidden="true"></span>
						<strong><?= Text::_('JDISABLED') ?></strong>
					</p>
				<?php endif ?>
			</div>
			<div class="col">
				<h4><?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_LOCKED') ?></h4>
				<?php if ($this->item?->locked): ?>
					<p class="text-danger hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_LBL_LOCKED_TOOLTIP') ?>">
						<span class="fa fa-lock" aria-hidden="true"></span>
						<strong><?= Text::_('COM_ONTHOS_ITEM_LBL_LOCKED') ?></strong>
					</p>
				<?php else: ?>
					<p class="text-success hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_LBL_UNLOCKED_TOOLTIP') ?>">
						<span class="fa fa-lock-open" aria-hidden="true"></span>
						<strong><?= Text::_('COM_ONTHOS_ITEM_LBL_UNLOCKED') ?></strong>
					</p>
				<?php endif ?>
			</div>
			<div class="col">
				<h4><?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_PROTECTED') ?></h4>
				<?php if ($this->item?->protected): ?>
					<p class="text-danger hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_LBL_PROTECTED_TOOLTIP') ?>">
						<span class="fa <?= version_compare(JVERSION, '4.999.999', 'gt') ? 'fa-shield' : 'fa-door-closed' ?>" aria-hidden="true"></span>
						<strong><?= Text::_('COM_ONTHOS_ITEM_LBL_PROTECTED') ?></strong>
					</p>
				<?php else: ?>
					<p class="text-success hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_LBL_UNPROTECTED_TOOLTIP') ?>">
						<span class="fa <?= version_compare(JVERSION, '4.999.999', 'gt') ? 'fa-shield-halved' : 'fa-door-open' ?>" data-fa-transform="shrink-6" aria-hidden="true"></span>
						<strong><?= Text::_('COM_ONTHOS_ITEM_LBL_UNPROTECTED') ?></strong>
					</p>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>

