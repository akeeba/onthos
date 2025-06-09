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

if (!$this->item?->getManifestPath())
{
	return;
}

$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');
$unknownText = Text::_('COM_ONTHOS_ITEM_APP_UNKNOWN');

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

?>
<div class="<?= $this->cardWrapperClass ?>">
	<h3 class="<?= $this->cardHeaderClass ?>">
		<?= Text::_('COM_ONTHOS_ITEM_HEAD_FILES_AND_DIRS') ?>
	</h3>

	<div class="<?= $this->cardBodyClass ?>">
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
	</div>
</div>

