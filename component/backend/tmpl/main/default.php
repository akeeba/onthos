<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @var \Akeeba\Component\Onthos\Administrator\View\Main\HtmlView $this
 */

/** @var ExtensionInterface $item */

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

$item = $this->getModel()->getExtensionByDetails('language', 'el-GR', clientId: 3);

$state = !($item instanceof ExtensionInterface)
	? 0
	: ($item?->isDiscovered() ? -1 : ($item?->isInstalled() ? 1 : 0));

$getFilePrintable = function ($value) use ($item): string {
	$exists       = @file_exists(JPATH_ROOT . '/' . $value);
	$reallyExists = $item?->fileReallyExists(JPATH_ROOT . '/' . $value);

	if ($reallyExists)
	{
		$icon  = 'fa-circle-check';
		$value = "<span class=\"text-success\">" . $this->escape($value) . "</span>";
	}
	elseif ($exists)
	{
		$icon  = 'fa-link-slash';
		$value = "<span class=\"text-warning\">" . $this->escape($value) . "</span>";
	}
	else
	{
		$icon  = 'fa-circle-xmark';
		$value = "<span class=\"text-danger\">" . $this->escape($value) . "</span>";
	}

	return "<span class=\"fa $icon\" aria-hidden=\"true\"></span> $value</li>";
};

$printArray = function ($array) use ($item, $getFilePrintable): void {
	if (empty($array))
	{
		echo "<span class=\"small text-muted\">N/A</span>";

		return;
	}

	echo "<ul class=\"list-unstyled\">\n";

	foreach ($array as $value)
	{
		echo "<li>" . $getFilePrintable($value) . "</li>\n";
	}

	echo "</ul>\n";
}

?>

<div class="card">
	<h3 class="card-header bg-secondary text-white">
		Extension Identity:
		<?= $this->escape($item?->getName() ?? strtoupper($item?->name ?? 'UNKNOWN')) ?>
	</h3>
	<div class="card-body">

		<?php
		$minRows     = 2;
		$isPlugin    = $item?->type === 'plugin';
		$hasClientId = !in_array($item?->type, ['component', 'file', 'files', 'library', 'package', 'plugin']);

		if ($isPlugin) $minRows++;
		if ($hasClientId) $minRows++;

		?>

		<div class="row row-cols-1 row-cols-sm-<?= intval(ceil($minRows/2)) ?> row-cols-md-<?= $minRows ?>">
			<div class="col">
				<h4>Extension Type</h4>
				<p>
					<?= $this->escape($item?->type ?? 'UNKNOWN') ?>
				</p>
			</div>
			<div class="col">
				<h4>Element</h4>
				<p>
					<?= $this->escape($item?->element ?? 'UNKNOWN') ?>
				</p>
			</div>
			<?php if ($isPlugin): ?>
			<div class="col">
				<h4>Folder</h4>
				<p>
					<?= $this->escape($item?->folder ?? 'UNKNOWN') ?>
				</p>
			</div>
			<?php endif; ?>
			<?php if ($hasClientId): ?>
			<div class="col">
				<h4>
					Application
				</h4>
				<?php if ($item?->client_id === 1): ?>
					<p>
						Administrator
					</p>
				<?php elseif ($item?->client_id === 0): ?>
					<p>
						Site
					</p>
				<?php elseif ($item?->client_id === 3): ?>
					<p>
						API
					</p>
				<?php else: ?>
					Application ID <?= $this->escape($item?->client_id ?? 'UNKNOWN') ?>
				<?php endif ?>
			</div>
			<?php endif ?>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4>State</h4>
				<?php if ($state == 1): ?>
					<p class="text-success">
						<span class="fa fa-circle-check" aria-hidden="true"></span>
						<strong>Installed</strong>
					</p>
				<?php elseif ($state == -1): ?>
					<p class="text-warning">
						<span class="fa fa-triangle-exclamation" aria-hidden="true"></span>
						Discovered
					</p>
				<?php else: ?>
					<p class="text-danger">
						<span class="fa fa-circle-exclamation" aria-hidden="true"></span>
						Broken
					</p>
				<?php endif; ?>
			</div>
			<div class="col">
				<h4>Package Status</h4>
				<?php if (!empty($item?->package_id ?? null)): ?>
					<p class="text-success">
						<span class="fa fa-link" aria-hidden="true"></span>
						<strong>Linked To Package</strong>
					</p>
				<?php elseif(!($item?->isOrphan() ?? true)): ?>
					<p class="text-muted">
						<span class="fa fa-square-up-right" aria-hidden="true"></span>
						<strong>Top-level Extension</strong>
					</p>
				<?php else: ?>
					<p class="text-danger">
						<span class="fa fa-link-slash" aria-hidden="true"></span>
						<strong>Orphaned Extension</strong>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4>Enabled State</h4>
				<?php if ($item?->enabled): ?>
					<p class="text-success">
						<span class="fa fa-circle-check" aria-hidden="true"></span>
						<strong>Enabled</strong>
					</p>
				<?php else: ?>
					<p class="text-danger">
						<span class="fa fa-circle-xmark" aria-hidden="true"></span>
						<strong>Disabled</strong>
					</p>
				<?php endif ?>
			</div>
			<div class="col">
				<h4>Lock State</h4>
				<?php if ($item?->locked): ?>
					<p class="text-danger">
						<span class="fa fa-lock" aria-hidden="true"></span>
						<strong>Locked</strong>
					</p>
				<?php else: ?>
					<p class="text-success">
						<span class="fa fa-lock-open" aria-hidden="true"></span>
						<strong>Unlocked</strong>
					</p>
				<?php endif ?>
			</div>
		</div>

		<hr />

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4>XML Manifest</h4>
				<p>
					<?php if ($item?->getManifestPath()): ?>
						<?= $getFilePrintable($item?->getManifestPath()) ?>
					<?php else: ?>
						<span class="small text-muted">N/A</span>
					<?php endif; ?>
				</p>
			</div>
			<div class="col">
				<h4>Installation Script</h4>
				<p>
					<?php if ($item?->getScriptPath()): ?>
						<?= $getFilePrintable($item?->getScriptPath()) ?>
					<?php else: ?>
						<span class="small text-muted">N/A</span>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4>Directories</h4>
				<?php
				$printArray($item?->getDirectories()) ?>
			</div>
			<div class="col">
				<h4>Files</h4>
				<?php
				$printArray($item?->getFiles()) ?>
			</div>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4>Language Files</h4>
				<?php
				$printArray($item?->getLanguageFiles()) ?>
			</div>
			<div class="col">
				<h4>Media Directories</h4>
				<?php
				$printArray($item?->getMediaPaths()) ?>
			</div>
		</div>

		<div class="row row-cols-1 row-cols-md-2">
			<div class="col">
				<h4>Database Tables</h4>
				<?php if ($item?->getTables()): ?>
				<ul class="list-unstyled">
					<?php foreach($item?->getTables() as $table): ?>
					<li>
						<span class="font-monospace">
							<?= $this->escape($table) ?>
						</span>
						<span class="ms-3 small text-muted font-monospace d-block d-md-none">
							<?= $this->escape(Factory::getContainer()->get(DatabaseInterface::class)->replacePrefix($table)) ?>
						</span>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php else: ?>
					<span class="small text-muted">N/A</span>
				<?php endif ?>
			</div>
			<div class="col d-none d-md-block">
				<h4 aria-hidden="true">&nbsp;</h4>
				<?php if ($item?->getTables()): ?>
					<ul class="list-unstyled">
						<?php foreach($item?->getTables() as $table): ?>
							<li>
								<span class="text-muted font-monospace">
									<?= $this->escape(Factory::getContainer()->get(DatabaseInterface::class)->replacePrefix($table)) ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<span class="small text-muted">N/A</span>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>
