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

use Akeeba\Component\Onthos\Administrator\View\Item\HtmlView;
use Joomla\CMS\Language\Text;

if (!$this->item?->getManifestPath())
{
	return;
}

$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');
$unknownText   = Text::_('COM_ONTHOS_ITEM_APP_UNKNOWN');
$manifest      = $this->item->getManifest();
?>
<div class="<?= $this->cardWrapperClass ?>">
	<h3 class="<?= $this->cardHeaderClass ?>">
		<?= Text::_('COM_ONTHOS_ITEM_HEAD_MANIFEST_DETAILS') ?>
	</h3>

	<div class="<?= $this->cardBodyClass ?>">
		<div class="container">
			<?php foreach (HtmlView::MANIFEST_METADATA_FIELDS as $field): ?>
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
	</div>
</div>
