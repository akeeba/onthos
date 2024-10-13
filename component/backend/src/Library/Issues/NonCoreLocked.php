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
 * Non-core locked test.
 *
 * Non-core extensions marked as locked.
 *
 * @since   1.0.0
 */
class NonCoreLocked extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::NOTICE;
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		return !$this->extension->isCore() && $this->extension->locked;
	}

	public function getDetailsTemplate(): string
	{
		// TODO Unlock the extension

		return parent::getDetailsTemplate();
	}


}