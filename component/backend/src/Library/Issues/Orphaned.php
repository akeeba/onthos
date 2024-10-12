<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;

defined('_JEXEC') || die;

/**
 * Orphaned extension test.
 *
 * @see  ExtensionInterface::isOrphan()
 *
 * @since   1.0.0
 */
class Orphaned implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __invoke(ExtensionInterface $extension): bool
	{
		return $extension->isOrphan();
	}
}