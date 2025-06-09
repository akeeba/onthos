<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Invalid children test.
 *
 * A package which has extensions claiming to belong to it, but they are not listed in its manifest.
 *
 * @since   1.0.0
 */
class InvalidChildren extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::WARNING;
	}


	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		if ($this->extension->type !== 'package' || $this->extension->getManifestPath() === null)
		{
			return false;
		}

		return $this->extension->hasInvalidChildren();
	}

	public function getDetailsTemplate(): string
	{
		return 'issues/invalidchildren';
	}


}