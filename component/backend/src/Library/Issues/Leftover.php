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
 * Wrong parent test.
 *
 * The extension is claimed by a package, but the extension claims to be owned by a different package.
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

		return !$existsDirs && !$existsFiles;
	}
}