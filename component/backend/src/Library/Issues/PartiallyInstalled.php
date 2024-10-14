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
 * Partially installed test.
 *
 * There are missing files or directories, but at least ONE of them is present.
 *
 * @since   1.0.0
 */
class PartiallyInstalled extends AbstractIssue implements IssueInterface
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
		if ($this->extension->isDiscovered())
		{
			return false;
		}

		$dirs   = $this->extension->getDirectories();
		$files  = $this->extension->getFiles();

		$existsDirs = empty($dirs) || array_reduce(
				$dirs,
				fn(bool $carry, string $directory): bool => $carry
				                                            || $this->extension->fileReallyExists(
						JPATH_ROOT . '/' . $directory
					),
				false
			);

		$existsFiles = empty($files) || array_reduce(
				$files,
				fn(bool $carry, string $file): bool => $carry
				                                       || $this->extension->fileReallyExists(
						JPATH_ROOT . '/' . $file
					),
				false
			);

		// If we are missing all directories and/or all files this test does not apply
		if (!$existsDirs || !$existsFiles)
		{
			return false;
		}

		$missingDirs = !empty($dirs) && array_reduce(
				$dirs,
				fn(bool $carry, string $directory): bool => $carry
				                                            || !$this->extension->fileReallyExists(
						JPATH_ROOT . '/' . $directory
					),
				false
			);

		if ($missingDirs)
		{
			return true;
		}

		$missingFiles = !empty($files) && array_reduce(
				$files,
				fn(bool $carry, string $file): bool => $carry
				                                       || !$this->extension->fileReallyExists(
						JPATH_ROOT . '/' . $file
					),
				false
			);

		return $missingFiles;
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

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		return 'commontemplates/reinstall';
	}
}