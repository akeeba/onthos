<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Package;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Leftover test.
 *
 * NONE of the files and directories are present.
 *
 * @since   1.0.0
 */
class Leftover extends AbstractIssue implements IssueInterface
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
		if ($this->extension->isDiscovered())
		{
			return false;
		}

		// A package's leftover status depends entirely on the presence of its subextensions
		if ($this->extension instanceof Package)
		{
			return count($this->extension->getSubextensionObjects()) == 0;
		}

		$dirs  = $this->extension->getDirectories();
		$files = $this->extension->getFiles();

		$existsDirs = !empty($dirs)
		              && array_reduce(
			              $dirs,
			              fn(bool $carry, string $directory): bool => $carry
			                                                          || $this->extension->fileReallyExists(
					              JPATH_ROOT . '/' . $directory
				              ),
			              false
		              );

		$existsFiles = !empty($files)
		               && array_reduce(
			               $files,
			               fn(bool $carry, string $file): bool => $carry
			                                                      || $this->extension->fileReallyExists(
					               JPATH_ROOT . '/' . $file
				               ),
			               false
		               );

		return !$existsDirs && !$existsFiles;
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

	public function getDetailsTemplate(): string
	{
		// TODO Forcibly uninstall the extension with Onthos.

		return parent::getDetailsTemplate();
	}


}