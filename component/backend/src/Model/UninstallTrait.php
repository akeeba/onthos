<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Model;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Package;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Installer\Administrator\Model\ManageModel;
use Joomla\Database\DatabaseDriver;
use Throwable;

defined('_JEXEC') || die;

trait UninstallTrait
{
	/**
	 * Standard extension uninstallation.
	 *
	 * Politeness level: “Please uninstall”.
	 *
	 * Unprotects and unlocks the extension before trying to uninstall it. It will work with most extensions.
	 *
	 * @param   ExtensionInterface  $extension
	 *
	 * @return  void
	 * @throws \Exception
	 * @since   1.0.0
	 */
	public function uninstall(ExtensionInterface $extension): void
	{
		// First, we will unprotect and unlock the extension.
		$extension->setFieldName('protected', 0);
		$extension->setFieldName('locked', 0);

		// Then, we will call Joomla's ManageModel::remove()
		/**
		 * @var CMSApplication      $app
		 * @var MVCFactoryInterface $factory
		 * @var ManageModel         $model
		 */
		$app     = Factory::getApplication();
		$factory = $app->bootComponent('com_installer')->getMVCFactory();
		$model   = $factory->createModel('Manage', 'Administrator', ['ignore_request' => true]);

		try
		{
			$model->remove([$extension->extension_id]);
		}
		finally
		{
			$this->zapInstallerInstance();
		}
	}

	/**
	 * Extension uninstallation ignoring the script.
	 *
	 * Politeness level: “I hope you uninstall”.
	 *
	 * First, it removes the script. Then, it unprotects and unlocks the extension before trying to uninstall it. This
	 * is designed to work with leftover installed extensions whose script is no longer compatible with the current
	 * Joomla! version. If you are running a site for more than 5 years you _definitely_ have one of these extensions
	 * lurking around in a dark, damp corner of your dungeon, er, site.
	 *
	 * @param   ExtensionInterface  $extension
	 *
	 * @return  void
	 * @throws \Exception
	 * @since   1.0.0
	 */
	public function uninstallNoScript(ExtensionInterface $extension): void
	{
		$scriptFile = $extension->getScriptPath();

		if (!$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $scriptFile))
		{
			throw new \RuntimeException(Text::_('COM_ONTHOS_ITEM_ERR_CANNOT_DELETE', htmlentities($scriptFile)));
		}

		$this->uninstall($extension);
	}

	/**
	 * Forced Extension uninstallation.
	 *
	 * Politeness level: “I wasn't asking”.
	 *
	 * 🚨 DANGER! 🚨 This is the very definition of EXTREMELY DANGEROUS. It should be your last resort, and only ever
	 * used after taking a backup, testing it works, and keeping three copies of it outside your site.
	 *
	 * This little bastard will remove the extension's tables, the script file, the XML manifest, the extension's
	 * media directory, the extension's translation files, and the extension's files and folders. Finally, it will
	 * remove its `#__extensions` record.
	 *
	 * There are SO MANY things which can go wrong. It is marginally safer than doing everything by hand with an SFTP
	 * client and a database client, and only because it will not forget to perform any of these steps. You should
	 * really, REALLY not use it unless you are certifiably insane or absolutely desperate.
	 *
	 * @param   ExtensionInterface  $extension
	 *
	 * @return  void
	 * @throws \Exception
	 * @since   1.0.0
	 */
	public function uninstallForced(ExtensionInterface $extension): void
	{
		// Packages are special. We first need to remove their contained extensions, then the package itself.
		if ($extension instanceof Package)
		{
			foreach ($extension->getSubextensionObjects() as $subExtension)
			{
				$this->uninstallForced($subExtension);
			}
		}

		// Remove database tables
		$this->forcedUninstallDatabaseTables($extension);

		// Remove media files
		$this->forcedUninstallMediaFiles($extension);

		// Remove language files
		$this->forcedUninstallLanguageFiles($extension);

		// Remove extension files and directories
		$this->forcedUninstallFiles($extension);
		$this->forcedUninstallDirectories($extension);

		// Remove script file
		if ($extension->getScriptPath())
		{
			$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $extension->getScriptPath());
		}

		// Remove the manifest
		if ($extension->getManifestPath())
		{
			$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $extension->getManifestPath());
		}

		// Remove the extensions table record
		$this->removeRecord($extension);
	}

	/**
	 * Remove the database record from the `#__extensions` table.
	 *
	 * If this is a module: it will remove all instances of the module.
	 *
	 * If this is a component: it will remove any `#__assets` and `#__menu` records.
	 *
	 * This is only really meant to be used for those cases where you have a pesky leftover database record and don't
	 * want to take your chances messing up your database by removing the `#__extensions` table record improperly.
	 *
	 * @param   ExtensionInterface  $extension
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function removeRecord(ExtensionInterface $extension): void
	{
		// TODO
		throw new \RuntimeException('This uninstallation method is not yet implemented.');
	}

	/**
	 * Remove an extension's files.
	 *
	 * @param   ExtensionInterface  $extension  The extension we are working with.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function forcedUninstallFiles(ExtensionInterface $extension): void
	{
		$extensionFiles = $extension->getFiles();

		if (empty($extensionFiles))
		{
			return;
		}

		foreach ($extensionFiles as $extensionFile)
		{
			$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $extensionFile);
		}
	}

	/**
	 * Remove an extension's directories.
	 *
	 * @param   ExtensionInterface  $extension  The extension we are working with.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function forcedUninstallDirectories(ExtensionInterface $extension): void
	{
		$extensionDirs = $extension->getDirectories();

		if (empty($extensionDirs))
		{
			return;
		}

		foreach ($extensionDirs as $extensionDir)
		{
			$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $extensionDir);
		}
	}

	/**
	 * Remove an extension's language files.
	 *
	 * @param   ExtensionInterface  $extension  The extension we are working with.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function forcedUninstallLanguageFiles(ExtensionInterface $extension): void
	{
		$languageFiles = $extension->getLanguageFiles();

		if (empty($languageFiles))
		{
			return;
		}

		foreach ($languageFiles as $languageFile)
		{
			$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $languageFile);
		}
	}

	/**
	 * Remove an extension's media files.
	 *
	 * @param   ExtensionInterface  $extension  The extension we are working with.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function forcedUninstallMediaFiles(ExtensionInterface $extension): void
	{
		$mediaPaths = $extension->getMediaPaths();

		if (empty($mediaPaths))
		{
			return;
		}

		foreach ($mediaPaths as $mediaPath)
		{
			$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $mediaPath);
		}
	}

	/**
	 * Uninstalls an extension's database tables.
	 *
	 * @param   ExtensionInterface  $extension  The extension we are working with.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function forcedUninstallDatabaseTables(ExtensionInterface $extension): void
	{
		$tables = $extension->getTables();

		if (empty($tables))
		{
			return;
		}

		/** @var DatabaseDriver $db */
		$db        = $this->getDatabase();
		$dropTable = match ($db->getServerType())
		{
			'mysql' => function (string $tableName) use ($db) {
				try
				{
					$db->setQuery('SET FOREIGN_KEY_CHECKS = 0;')->execute();
					$db->dropTable($tableName, true);
				}
				catch (Throwable)
				{
					// We can swallow any errors.
				}
				finally
				{
					$db->setQuery('SET FOREIGN_KEY_CHECKS = 1;')->execute();
				}
			},
			'postgresql' => function (string $tableName) use ($db) {
				$query = 'DROP TABLE IF EXISTS ' . $db->quoteName($tableName) . ' CASCADE';
				$db->setQuery($query)->execute();
			},
			default => function (string $tableName) use ($db) {
				throw new \RuntimeException(
					Text::sprintf('COM_ONTHOS_ITEM_ERR_UNSUPPORTED_DB_SERVER', $db->getServerType())
				);
			}
		};

		foreach ($tables as $table)
		{
			$dropTable($table);
		}
	}

	/**
	 * Safely delete a target, no matter if it's a file, directory, or symlink.
	 *
	 * This is especially important for symlinks! You want to delete a directory symlink, not recursively delete the
	 * directory symlink's contents like Joomla! does in Folder::delete().
	 *
	 * @param   string  $fileOrDir
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	private function safeRecursiveUnlink(string $fileOrDir): bool
	{
		// Handle symlinks gracefully. Especially important for DIRECTORY symlinks!
		if (@is_link($fileOrDir))
		{
			return @unlink($fileOrDir);
		}

		// Fake success for non-existent files
		if (!@file_exists($fileOrDir))
		{
			return true;
		}

		// Delete regular files
		if (@is_file($fileOrDir))
		{
			return @unlink($fileOrDir);
		}

		// WTF is it if not a link, file, or folder? An alien?!
		if (!@is_dir($fileOrDir))
		{
			return false;
		}

		// Directories are removed recursively
		foreach (new \DirectoryIterator($fileOrDir) as $fileInfo)
		{
			// Skip . and .. to avoid tragic screw-ups.
			if ($fileInfo->isDot())
			{
				continue;
			}

			// Delete the file or folder, but stop if deleting fails!
			if (!$this->safeRecursiveUnlink($fileInfo->getPathname()))
			{
				return false;
			}
		}

		// Finally, delete the folder we started with.
		return rmdir($fileOrDir);
	}
}