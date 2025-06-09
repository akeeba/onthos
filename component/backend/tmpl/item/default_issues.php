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

$isJoomla5Plus = version_compare(JVERSION, '4.999.999', 'gt');
$unknownText = Text::_('COM_ONTHOS_ITEM_APP_UNKNOWN');

$issues = $this->item?->issues?->getIssues() ?? [];

if (!count($issues))
{
	return;
}
?>
<div class="<?= $this->cardWrapperClass ?>">
	<h3 class="<?= $this->cardHeaderClass ?>">
		<?= Text::_('COM_ONTHOS_ITEM_HEAD_ISSUES') ?>
	</h3>

	<div class="<?= $this->cardBodyClass ?>">
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
	</div>
</div>