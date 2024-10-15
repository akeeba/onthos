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
 * Missing language files test.
 *
 * @see  ExtensionInterface::isMissingLanguages()
 *
 * @since   1.0.0
 */
class MissingLanguage extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		// If there is no manifest I cannot be sure about languages.
		if ($this->extension->getManifestPath() === null)
		{
			return false;
		}

		// If no language files have been declared then nothing is missing.
		$languageFiles = $this->extension->getLanguageFiles();

		if (empty($languageFiles))
		{
			return false;
		}

		return array_reduce(
			$languageFiles,
			fn(bool $carry, string $language) => $carry || !$this->extension->fileReallyExists(JPATH_ROOT . '/' . $language),
			false
		);
	}

	public function getSeverity(): string
	{
		if ($this->extension->isCore())
		{
			return LogLevel::DEBUG;
		}

		return parent::getSeverity();
	}


	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		return 'issues/reinstall';
	}
}