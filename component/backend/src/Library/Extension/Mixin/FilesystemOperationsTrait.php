<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin;

defined('_JEXEC') || die;

/**
 * Trait providing various filesystem operations.
 *
 * @since  1.0.0
 */
trait FilesystemOperationsTrait
{
	/**
	 * Does the file or folder **REALLY** exist?
	 *
	 * This takes into consideration symlinks. If the symlink exists, but where it's pointing to does not then we return
	 * false. The idea is that you may have a broken symlinks which does exist as a symlink but it points to nowhere,
	 * therefore it has to be cleaned up.
	 *
	 * @param   string  $file
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	final public function fileReallyExists(string $file): bool
	{
		if (!@file_exists($file))
		{
			return false;
		}

		if (!@is_link($file))
		{
			return true;
		}

		return @file_exists(@readlink($file));
	}

	/**
	 * Filters an array of files to include only the normalised paths to unique, existing files.
	 *
	 * @param   array  $files   The array of files to filter
	 * @param   bool   $rebase  Should I rebase the array contents to JPATH_ROOT?
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	final protected function filterFilesArray(array $files, bool $rebase = true): array
	{
		return $this->filterFileOrDirArray($files, rebase: $rebase);
	}

	/**
	 * Filters an array of directories to include only the normalised paths to unique, existing directories.
	 *
	 * @param   array  $dirs    The array of directories to filter
	 * @param   bool   $rebase  Should I rebase the array contents to JPATH_ROOT?
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	final protected function filterDirectoriesArray(array $dirs, bool $rebase = true): array
	{
		return $this->filterFileOrDirArray($dirs, isFile: false, rebase: $rebase);
	}

	/**
	 * Rebase a file or folder path as relative to JPATH_ROOT
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	final protected function rebaseToRoot(string $path)
	{
		$path = $this->normalisePath($path);
		$root = $this->normalisePath(JPATH_ROOT);

		if (!str_starts_with($path, $root))
		{
			return $path;
		}

		return substr($path, strlen($root) + 1);
	}

	/**
	 * Filters an array of files or directories for existing elements, returning their relative paths to JPATH_ROOT.
	 *
	 * Symlinks will be reported if they exist, even if they point to a non-existent location.
	 *
	 * @param   array  $files   The files array to scan.
	 * @param   bool   $isFile  Is this an array of files?
	 * @param   bool   $rebase  Should I rebase everything to JPATH_ROOT?
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	private function filterFileOrDirArray(array $files, bool $isFile = true, bool $rebase = true): array
	{
		$files = array_filter($files);
		$files = array_map([$this, 'normalisePath'], $files);
		$files = array_unique($files);
		$files = array_filter(
			$files,
			function ($file) use ($isFile): bool {
				if (!@file_exists($file))
				{
					return false;
				}

				// Symlinks are not tested here. Use self::fileReallyExists() to test if they are broken.
				if (@is_link($file))
				{
					return true;
				}

				return $isFile ? @is_file($file) : @is_dir($file);
			}
		);

		return $rebase ? array_map([$this, 'rebaseToRoot'], $files) : $files;
	}

	/**
	 * Normalise the path.
	 *
	 * Makes a Windows path more UNIX-like, by turning backslashes to forward slashes. It takes into account UNC paths,
	 * e.g. \\myserver\some\folder becomes \\myserver/some/folder.
	 *
	 * This function will also fix paths with multiple slashes, e.g. convert /var//www////html to /var/www/html
	 *
	 * @param   string  $path  The path to transform
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	private function normalisePath(string $path): string
	{
		$is_unc = false;

		if (PHP_OS_FAMILY == 'Windows')
		{
			// Is this a UNC path?
			$is_unc = (substr($path, 0, 2) == '\\\\') || (substr($path, 0, 2) == '//');

			// Change potential windows directory separator
			if ((strpos($path, '\\') > 0) || (substr($path, 0, 1) == '\\'))
			{
				$path = strtr($path, '\\', '/');
			}
		}

		// Remove multiple slashes
		$path = str_replace('///', '/', $path);
		$path = str_replace('//', '/', $path);

		// Fix UNC paths
		if ($is_unc)
		{
			$path = '//' . ltrim($path, '/');
		}

		return rtrim($path, '/\\');
	}
}