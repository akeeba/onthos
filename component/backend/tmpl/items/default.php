<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var \Akeeba\Component\Onthos\Administrator\View\Items\HtmlView $item */

use Akeeba\Component\Onthos\Administrator\Helper\OnthosHelper;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

$this->tableColumnsAutohide();
$this->tableRowsMultiselect('#extensionsList');

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$nullDate  = Factory::getDbo()->getNullDate();
$baseUri   = Uri::root();
$i         = 0;
?>
<?= $this->loadAnyTemplate('commontemplates/warning') ?>

<form action="<?= Route::_('index.php?option=com_onthos&view=items'); ?>"
      method="post" name="adminForm" id="adminForm">

	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]) ?>
			</div>

			<?php if($this->isUnreliableCore): ?>
				<?= $this->loadAnyTemplate('commontemplates/jcorebug') ?>
			<?php endif; ?>

			<table class="table table-striped" id="extensionsList">
				<caption class="visually-hidden">
					<?= Text::_('COM_ONTHOS_ITEMS_TABLE_CAPTION'); ?>,
					<span id="orderedBy"><?= Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
					<span id="filteredBy"><?= Text::_('JGLOBAL_FILTERED_BY'); ?> </span>
				</caption>
				<thead>
				<tr>
					<td class="w-1 text-center">
						<?= HTMLHelper::_('grid.checkall'); ?>
					</td>

					<th scope="col">
						<?= Text::_('COM_INSTALLER_HEADING_TYPE') ?>
					</th>

					<th scope="col">
						<?= Text::_('COM_ONTHOS_ITEMS_FIELD_STATE') ?>
					</th>

					<th scope="col">
						<?= HTMLHelper::_('searchtools.sort', 'COM_ONTHOS_ITEMS_FIELD_NAME', 'name', $listDirn, $listOrder); ?>
					</th>

					<th scope="col" class="w-1 d-none d-md-table-cell">
						<?= HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as /** @var ExtensionInterface $item */ $item) : ?>
				<?php
					$version       = $item->manifest_cache->get('version');
					$creationDate  = $item->manifest_cache->get('creationDate');
					$author        = $item->manifest_cache->get('author');
					$authorEmail   = $item->manifest_cache->get('authorEmail');
					$authorUrl     = $item->manifest_cache->get('authorUrl');
					$description   = $item->manifest_cache->get('description');
					?>
				<tr class="row<?= $i++ % 2; ?>">
					<td class="text-center">
						<?= HTMLHelper::_('grid.id', $i, $item->extension_id, false, 'cid', 'cb', $item->getName() ?? ''); ?>
					</td>

					<td>
						<div>
							<span class="fa fs-3 <?= $this->getTypeIcon($item) ?> fa-fw hasTooltip" aria-hidden="true"
								  title="<?= Text::_('COM_INSTALLER_TYPE_' . $item->type) ?>">
							</span>
							<span class="visually-hidden">
								<?= Text::_('COM_INSTALLER_TYPE_' . $item->type) ?>
							</span>

							<?php if (in_array($item->type, ['language', 'module', 'template'])): ?>
								<span class="fa fs-4 <?= $this->getApplicationIcon($item) ?> fa-fw text-secondary ms-2 hasTooltip"
									  aria-hidden="true"
									  title="<?= $this->getApplicationName($item) ?>">
								</span>
								<span class="visually-hidden">
									<?= $this->getApplicationName($item) ?>
								</span>
							<?php endif ?>
						</div>

						<?php if ($item->type === 'plugin'): ?>
						<div class="font-monospace mt-1 text-info">
							<?= $this->escape($item->folder) ?>
						</div>
						<?php endif; ?>
					</td>

					<td>
						<div class="d-flex gap-2 flex-wrap">
							<?= OnthosHelper::published($item->isDiscovered() ? -1 : $item->enabled, $i, !$item->isDiscovered()) ?>
							<?= OnthosHelper::protected($item->protected, $i, !$item->isDiscovered()) ?>
							<?= OnthosHelper::locked($item->locked, $i, !$item->isDiscovered()) ?>
						</div>
					</td>

					<td>
						<div class="mb-1 pb-1 border-bottom">
							<?php if ($item->isCore()): ?>
							<span class="fa fa-joomla fa-fw me-1 text-secondary hasTooltip" aria-hidden="true"
								  title="<?= Text::_('COM_ONTHOS_ITEM_LBL_CORE') ?>"
							></span>
							<?php endif; ?>

							<a href="<?= Route::_('index.php?option=com_onthos&view=item&id=' . $item->extension_id) ?>"
							   class="fw-semibold">
								<?= $this->escape(strip_tags($item->getName())) ?>
							</a>

							<?php if ($description): ?>
							<span class="hasTooltip fa fa-info-circle ms-3 text-body-tertiary" aria-hidden="true"
								  title="<?= $this->escape(Text::_($description)) ?>"
							></span>
							<?php endif ?>
						</div>

						<div class="text-secondary small d-flex flex-row gap-2">
							<div style="min-width: 7em">
								<span class="fa fa-hashtag hasTooltip" aria-hidden="true"
									  title="<?= Text::_('JVERSION') ?>"
								></span>
								<span class="visually-hidden"><?= Text::_('JVERSION') ?></span>
								<?= $this->escape($version) ?: Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
							</div>

							<div style="min-width: 9em">
								<span class="fa fa-calendar hasTooltip" aria-hidden="true"
									  title="<?= Text::_('JDATE') ?>"
								></span>
								<span class="visually-hidden"><?= Text::_('JDATE') ?></span>
								<?= $this->escape($creationDate) ?: Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
							</div>

							<div>
								<span class="fa fa-user-pen hasTooltip" aria-hidden="true"
									  title="<?= Text::_('JAUTHOR') ?>"
								></span>
								<span class="visually-hidden"><?= Text::_('JAUTHOR') ?></span>

								<?php if (!empty($author) && !empty($authorUrl)): ?>
									<a href="<?= $this->escape($authorUrl) ?>"
									   target="_blank"
									   class="link-secondary"
									>
										<?= $this->escape($author) ?: Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
									</a>
								<?php elseif (!empty($author) && !empty($authorEmail)): ?>
									<a href="email:<?= $this->escape($authorEmail) ?>"
									   class="link-secondary"
									>
										<?= $this->escape($author) ?: Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
									</a>
								<?php else: ?>
									<?= $this->escape($author) ?: Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
								<?php endif; ?>
							</div>
						</div>

						<?php if ($item->getParentPackage() instanceof ExtensionInterface):?>
						<div>
							<span class="fa fa-arrows-split-up-and-left hasTooltip" aria-hidden="true"
								  title="<?= Text::_('COM_ONTHOS_ITEM_LBL_LINKED') ?>"
							></span>
							<span class="visually-hidden"><?= Text::_('COM_ONTHOS_ITEM_LBL_LINKED') ?></span>
							<a href="<?= Route::_('index.php?option=com_onthos&view=items&filter[package_id]=' . $item->getParentPackage()->extension_id) ?>"
							   class="link-info"
							>
							<?= $this->escape($item->getParentPackage()->getName()) ?>
							</a>
							<span class="small muted">
								(#<?= $this->escape($item->getParentPackage()->extension_id) ?>)
							</span>
						</div>
						<?php endif ?>
						<?php if ($item->issues->getIssues()): ?>
						<div class="d-flex gap-3">
							<?php foreach ($item->issues->getIssues() as $issue): ?>
							<?php
							$class = match ($issue->getSeverity()) {
								'emergency', 'alert' => 'text-danger fw-bold',
								'critical'  => 'text-danger fw-semibold',
								'error'     => 'text-danger',
								'warning'   => 'text-warning fw-semibold',
								'notice'    => 'text-warning',
								'info'      => 'text-info',
								'debug'     => 'text-muted',
							}
							?>
							<div class="<?= $class ?>">
								<span class="<?= $issue->getIcon() ?>" aria-hidden="true"></span>
								<?= $issue->getLabel() ?>
							</div>
							<?php endforeach; ?>
						</div>
						<?php endif ?>
					</td>

					<th scope="col" class="w-1 d-none d-md-table-cell">
						<?= intval($item->extension_id) ?>
					</th>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?= $this->pagination->getListFooter(); ?>

			<input type="hidden" name="task" value=""> <input type="hidden" name="boxchecked" value="0">
			<?= HTMLHelper::_('form.token'); ?>
		</div>
	</div>

</form>
