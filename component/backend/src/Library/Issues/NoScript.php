<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;

defined('_JEXEC') || die;

/**
 * No installation script test.
 *
 * The script file is declared but missing.
 *
 * @see  ExtensionInterface::isMissingLanguages()
 *
 * @since   1.0.0
 */
class NoScript extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		$scriptPath = $this->extension->getScriptPath();

		if ($scriptPath === null)
		{
			return false;
		}

		return !$this->extension->fileReallyExists(JPATH_ROOT . '/' . $scriptPath);
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