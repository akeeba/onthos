<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Helper\AdoptionHelper;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Discovered.
 *
 * This extension's files exist on your site, but the extension is not installed.
 *
 * @since   1.0.0
 */
class Discovered extends AbstractIssue implements IssueInterface
{
	use AdoptionTrait;

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::DEBUG;
	}


	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		return $this->extension->state == -1;
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		return 'issues/discovered';
	}
}