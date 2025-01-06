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
 * Wrong parent test.
 *
 * The extension is claimed by a package, but the extension claims to be owned by a different package.
 *
 * @since   1.0.0
 */
class WrongParent extends AbstractIssue implements IssueInterface
{
	use AdoptionTrait;

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
		// Packages and core extensions are exempt by definition
		if ($this->extension->type !== 'package' || $this->extension->isCore() === null)
		{
			return false;
		}

		return AdoptionHelper::hasWrongPackageId($this->extension->extension_id);
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		return 'issues/adopt';
	}
}