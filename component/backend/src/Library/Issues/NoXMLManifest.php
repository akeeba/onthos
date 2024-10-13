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
 * No XML Manifest test.
 *
 * The XML manifest is missing
 *
 * @see  ExtensionInterface::isMissingLanguages()
 *
 * @since   1.0.0
 */
class NoXMLManifest extends AbstractIssue implements IssueInterface
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
		$manifestPath = $this->extension->getManifestPath();

		if ($manifestPath === null)
		{
			return false;
		}

		return !$this->extension->fileReallyExists(JPATH_ROOT . '/' . $manifestPath);
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		return 'commontemplates/reinstall';
	}
}