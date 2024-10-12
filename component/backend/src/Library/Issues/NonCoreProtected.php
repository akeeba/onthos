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
 * Non-core protected test.
 *
 * Non-core extensions marked as protected.
 *
 * @see  ExtensionInterface::isOrphan()
 *
 * @since   1.0.0
 */
class NonCoreProtected implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __invoke(ExtensionInterface $extension): bool
	{
		return !$extension->isCore() && $extension->protected;
	}
}