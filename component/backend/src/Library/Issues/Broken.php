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
 * Broken extension test.
 *
 * @see ExtensionInterface::isInstalled()
 * @see ExtensionInterface::isDiscovered()
 *
 * @since   1.0.0
 */
class Broken implements IssueInterface
{
	public function __invoke(ExtensionInterface $extension): bool
	{
		return !$extension->isInstalled() && !$extension->isDiscovered();
	}
}