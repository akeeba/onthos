<?php
/*
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

$issues = $this->item?->issues?->getIssues() ?? [];
$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');

$getFilePrintable = function ($value) use ($isJoomla5Plus): string {
	$exists       = @file_exists(JPATH_ROOT . '/' . $value);
	$reallyExists = $this->item?->fileReallyExists(JPATH_ROOT . '/' . $value);

	if ($reallyExists)
	{
		$icon  = $isJoomla5Plus ? 'fa-circle-check' : 'fa-check-circle';
		$class = 'text-success';
	}
	elseif ($exists)
	{
		$icon  = $isJoomla5Plus ? 'fa-link-slash' : 'fa-unlink';
		$class = 'text-warning';
	}
	else
	{
		$icon  = $isJoomla5Plus ? 'fa-circle-xmark' : 'fa-times-circle';
		$class = 'text-danger';
	}

	return <<<HTML
<span class="fa $icon" aria-hidden="true"></span>
<span class="$class">{$this->escape($value)}</span> 
HTML;
};

$printArray = function ($array) use ($getFilePrintable): void {
	if (empty($array))
	{
		$text = Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE');
		echo <<<HTML
<span class="small text-muted">$text</span>
HTML;

		return;
	}

	echo <<<HTML
<ul class="list-unstyled">

HTML;

	foreach ($array as $value)
	{
		echo <<<HTML
<li>{$getFilePrintable($value)}</li>

HTML;
	}

	echo "</ul>";
};

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

<div class="card">
	<h2 class="card-header bg-secondary text-white">
		<?= Text::_('COM_ONTHOS_ITEM_TOP_HEADER') ?> &ndash;
		<span class="fs-3 ms-2 text-tertiary">
		<?= $this->escape($this->item?->getName() ?? strtoupper($this->item?->name ?? 'UNKNOWN')) ?>
		</span>
	</h2>
	<div class="card-body">
		<h3 class="border-bottom border-2 mt-0 pb-2 mb-2">
			<?= Text::_('COM_ONTHOS_ITEM_HEAD_BASIC') ?>
		</h3>

		<?php if($this->item->isCore()): ?>
		<?= $this->loadAnyTemplate('commontemplates/jcorebug') ?>
		<?php endif ?>

		<?php
		$minRows     = 2;
		$isPlugin    = $this->item?->type === 'plugin';
		$hasClientId = !in_array($this->item?->type, ['component', 'file', 'files', 'library', 'package', 'plugin']);

		if ($isPlugin) $minRows++;
		if ($hasClientId) $minRows++;
		?>
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

		<?php if ($this->item?->getManifestPath()): ?>
			<?php $manifest = $this->item->getManifest() ?>
			<h3 class="border-bottom border-2 pb-2 my-2">
				<?= Text::_('COM_ONTHOS_ITEM_HEAD_MANIFEST_DETAILS') ?>
			</h3>

			<div class="container">
			<?php foreach (self::MANIFEST_METADATA_FIELDS as $field): ?>
				<?php
					$value = $manifest->$field;
					if (!$value instanceof SimpleXMLElement) continue;
					$value = trim((string) $value);
					if (empty($value)) continue;
					$translated = Text::_($value);
					$hasTranslation = strtolower($translated) !== strtolower($value);
				?>
				<div class="row mb-2">
					<div class="col-6 col-md-5 col-lg-4 col-xl-3 fw-semibold">
						<?= Text::_('COM_ONTHOS_ITEM_MANIFEST_FIELD_' . strtoupper($field)) ?>
					</div>
					<div class="col-6 col-md-7 col-lg-8 col-xl-9">
						<?php if ($hasTranslation): ?>
							<?= $translated ?>
							<br/>
							<span class="small text-muted my-1">
								(<code><?= htmlentities($value) ?></code>)
							</span>
						<?php elseif ($field === 'namespace'): ?>
							<span class="font-monospace"><?= htmlentities($value) ?></span>
							<?php
							$path = $manifest->$field->attributes()->path;
							if ($path instanceof SimpleXMLElement):
							?>
							<br />
								<span class="fw-semibold text-secondary">
									<?= Text::_('COM_ONTHOS_ITEM_MANIFEST_FIELD_NAMESPACE_PATH') ?>:
								</span>
								<code><?= $path ?></code>
							<?php endif; ?>
						<?php else: ?>
							<?= htmlentities($value) ?>
						<?php endif ?>
					</div>
				</div>
			<?php endforeach ?>
			</div>
		<?php endif ?>

		<h3 class="border-bottom border-2 pb-2 my-2">
			<?= Text::_('COM_ONTHOS_ITEM_HEAD_FILES_AND_DIRS') ?>
		</h3>

		<div class="alert alert-info small">
			<span class="fa fa-info-circle" aria-hidden="true"></span>
			<?= Text::_('COM_ONTHOS_ITEM_LBL_NA_IS_OK') ?>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4 class="hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_XML_MANIFEST_TOOLTIP') ?>">
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_XML_MANIFEST') ?>
				</h4>
				<p>
					<?php if ($this->item?->getManifestPath()): ?>
						<?= $getFilePrintable($this->item?->getManifestPath()) ?>
					<?php else: ?>
						<span class="small text-muted">
							<?= Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
						</span>
					<?php endif; ?>
				</p>
			</div>
			<div class="col">
				<h4 class="hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_SCRIPT_TOOLTIP') ?>">
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_SCRIPT') ?>
				</h4>
				<p>
					<?php if ($this->item?->getScriptPath()): ?>
						<?= $getFilePrintable($this->item?->getScriptPath()) ?>
					<?php else: ?>
						<span class="small text-muted">
							<?= Text::_('COM_ONTHOS_ITEM_LBL_NOT_APPLICABLE') ?>
						</span>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<?php $key = 'COM_ONTHOS_ITEM_SUBHEAD_DIRECTORIES_TOOLTIP' . ($this->item?->getManifestPath() ? '' : '_NO_XML') ?>
				<h4 class="hasTooltip" title="<?= Text::_($key) ?>">
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_DIRECTORIES') ?>
				</h4>
				<?php $printArray($this->item?->getDirectories()) ?>
			</div>
			<div class="col">
				<?php $key = 'COM_ONTHOS_ITEM_SUBHEAD_FILES_TOOLTIP' . ($this->item?->getManifestPath() ? '' : '_NO_XML') ?>
				<h4 class="hasTooltip" title="<?= Text::_($key) ?>">
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_FILES') ?>
				</h4>
				<?php $printArray($this->item?->getFiles()) ?>
			</div>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4 class="hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_LANGFILES_TOOLTIP') ?>">
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_LANGFILES') ?>
				</h4>
				<?php $printArray($this->item?->getLanguageFiles()) ?>
			</div>
			<div class="col">
				<h4 class="hasTooltip" title="<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_MEDIADIRS_TOOLTIP') ?>">
					<?= Text::_('COM_ONTHOS_ITEM_SUBHEAD_MEDIADIRS') ?>
				</h4>
				<?php $printArray($this->item?->getMediaPaths()) ?>
			</div>
		</div>

		<h3 class="border-bottom border-2 pb-2 my-2 hasTooltip"
			title="<?= Text::_('COM_ONTHOS_ITEM_HEAD_DATABASE_TOOLTIP') ?>"
		>
			<?= Text::_('COM_ONTHOS_ITEM_HEAD_DATABASE') ?>
		</h3>

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

		<?php if($this->item->getCanonicalUpdateServers() || $this->item->getUpdateSites()): ?>
			<h3 class="border-bottom border-2 pb-2 my-2 hasTooltip"
				title="<?= Text::_('COM_ONTHOS_ITEM_HEAD_UPDATES_TOOLTIP') ?>"
			>
				<?= Text::_('COM_ONTHOS_ITEM_HEAD_UPDATES') ?>
			</h3>

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
		<?php endif; ?>

		<?php if (count($this->item->issues->getIssues())): ?>
			<h3 class="border-bottom border-2 pb-2 my-2 hasTooltip">
				<?= Text::_('COM_ONTHOS_ITEM_HEAD_ISSUES') ?>
			</h3>

			<?php foreach ($issues as $issue): ?>
			<div class="mt-3 mb-4">
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
				<h4 class="<?= $class ?>">
					<span class="<?= $issue->getIcon() ?>" aria-hidden="true"></span>
					<?= $issue->getLabel() ?>
				</h4>
				<p class="ps-4 text-body"><?= $issue->getDescription() ?></p>
				<div class="ps-4 text-body">
					<?= $this->loadAnyTemplate(
						$issue->getDetailsTemplate(),
						false,
						[
							'issue'     => $issue,
							'extension' => $this->item,
						]
					) ?>
				</div>
			</div>
			<?php endforeach; ?>
		<?php endif?>

	</div>
</div>