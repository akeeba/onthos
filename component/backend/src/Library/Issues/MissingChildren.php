<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Missing children test.
 *
 * A package which is missing one or more of its child extensions.
 *
 * @since   1.0.0
 */
class MissingChildren extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::ERROR;
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

		return $this->extension->hasMissingSubextensions();
	}

	public function getDetailsTemplate(): string
	{
		// TODO Reinstall the package

		return parent::getDetailsTemplate();
	}


}