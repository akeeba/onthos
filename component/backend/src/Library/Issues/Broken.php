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
 * Broken extension test.
 *
 * @see ExtensionInterface::isInstalled()
 * @see ExtensionInterface::isDiscovered()
 *
 * @since   1.0.0
 */
class Broken extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::CRITICAL;
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		return !$this->extension->isInstalled() && !$this->extension->isDiscovered();
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getSeverity(): string
	{
		if ($this->extension->isCore())
		{
			return LogLevel::DEBUG;
		}

		return parent::getSeverity();
	}
}