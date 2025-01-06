<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

defined('_JEXEC') || die;

/**
 * Missing language files test.
 *
 * @since   1.0.0
 *
 */
class MissingMedia extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		$mediaFiles = $this->extension->getMediaPaths();

		if (empty($mediaFiles))
		{
			return false;
		}

		foreach ($mediaFiles as $mediaFile)
		{
			if (!$this->extension->fileReallyExists(JPATH_ROOT . '/' . $mediaFile))
			{
				return true;
			}
		}

		return false;
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