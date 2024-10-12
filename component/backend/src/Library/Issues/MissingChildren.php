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
 * Missing children test.
 *
 * A package which is missing one or more of its child extensions.
 *
 * @since   1.0.0
 */
class MissingChildren implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __invoke(ExtensionInterface $extension): bool
	{
		if ($extension->type !== 'package' || $extension->getManifestPath() === null)
		{
			return false;
		}

		// TODO

		return false;
	}
}