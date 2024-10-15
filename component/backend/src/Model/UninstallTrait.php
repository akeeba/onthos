<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Model;


use Akeeba\Component\Onthos\Administrator\Library\Extension\Component;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Module;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Package;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Fields\Administrator\Table\FieldTable;
use Joomla\Component\Fields\Administrator\Table\GroupTable;
use Joomla\Component\Installer\Administrator\Model\ManageModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use SimpleXMLElement;
use Throwable;

defined('_JEXEC') || die;

trait UninstallTrait
{
	/**
	 * Regular Joomla! extension uninstallation.
	 *
	 * @param   ExtensionInterface  $extension
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function joomlaUninstall(ExtensionInterface $extension): void
	{
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
	 * Unprotect and Uninstall.
	 *
	 * Unprotects and unlocks the extension before trying to uninstall it. It will work with most extensions.
	 *
	 * @param   ExtensionInterface  $extension
	 *
	 * @return  void
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function uninstall(ExtensionInterface $extension): void
	{
		// First, we will unprotect and unlock the extension.
		$extension->setFieldName('protected', 0);
		$extension->setFieldName('locked', 0);

		// Remove blockChildUninstall from the package's manifest.
		if ($extension instanceof Package)
		{
			// I am a package; remove my own <blockChildUninstall> tag
			$this->unblockChildUninstallInPackage($extension);
		}
		elseif ($extension->getParentPackage() instanceof Package)
		{
			// I belong to a package. Remove the parent package's <blockChildUninstall> tag.
			$this->unblockChildUninstallInPackage($extension->getParentPackage());
		}

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
	 * Remove Script and Uninstall.
	 *
	 * First, it removes the script. Then, it unprotects and unlocks the extension before trying to uninstall it. This
	 * is designed to work with leftover installed extensions whose script is no longer compatible with the current
	 * Joomla! version. If you are running a site for more than 5 years you _definitely_ have one of these extensions
	 * lurking around in a dark, damp corner of your dungeon, er, site.
	 *
	 * @param   ExtensionInterface  $extension
	 *
	 * @return  void
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function uninstallNoScript(ExtensionInterface $extension): void
	{
		$scriptFile = $extension->getScriptPath();

		if (
			!empty($scriptFile)
			&& @is_file(JPATH_ROOT . '/' . $scriptFile)
			&& !$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $scriptFile)
		)
		{
			throw new \RuntimeException(Text::_('COM_ONTHOS_ITEM_ERR_CANNOT_DELETE', htmlentities($scriptFile)));
		}

		$this->uninstall($extension);
	}

	/**
	 * Forced Uninstall.
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
	 * @throws Exception
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
		$manifestPath = $extension->getManifestPath();

		if (!empty($manifestPath) && @is_file(JPATH_ROOT . '/' . $manifestPath))
		{
			$this->safeRecursiveUnlink(JPATH_ROOT . '/' . $manifestPath);
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
		// Modules: remove all instances of the module
		if ($extension instanceof Module)
		{
			$this->removeModuleInstances($extension);
		}

		// Component: remove `#__assets` and `#__menu` records.
		if ($extension instanceof Component)
		{
			// Remove field values
			$this->removeComponentFieldValues($extension);

			// Remove fields
			$this->removeComponentFields($extension);

			// Remove field groups
			$this->removeComponentFieldGroups($extension);

			// Remove categories
			$this->removeComponentCategories($extension);

			// Remove menu records
			$this->removeComponentMenuRecords($extension);

			// Remove the component's asset
			$this->removeComponentAsset($extension);
		}

		$this->removeExtensionRecord($extension);
	}

	/**
	 * Removes the <blockChildUninstall> element from a package.
	 *
	 * @param   Package  $package
	 *
	 * @return  void
	 * @throws Exception
	 * @since   1.0.0
	 */
	private function unblockChildUninstallInPackage(Package $package): void
	{
		$filePath = $package->getManifestPath();

		if (!@is_file($filePath) || !@is_readable($filePath))
		{
			return;
		}

		$xmlString = @file_get_contents($filePath);

		if ($xmlString === false)
		{
			return;
		}

		$xml          = new SimpleXMLElement($xmlString);
		$blockElement = $xml->xpath('/extension/blockChildUninstall');

		if (!$blockElement)
		{
			return;
		}

		try
		{
			$dom = dom_import_simplexml($xml)->ownerDocument;
		}
		catch (Throwable)
		{
			return;
		}

		$nodeToRemove = $dom->getElementsByTagName('blockChildUninstall')->item(0);

		if (!$nodeToRemove)
		{
			return;
		}

		$nodeToRemove->parentNode->removeChild($nodeToRemove);

		$xml       = simplexml_import_dom($dom);
		$xmlString = $xml->asXML();

		@file_put_contents($filePath, $xmlString);
	}

	/**
	 * Removes instances of a given module from the database.
	 *
	 * This method identifies and deletes all instances of the module specified by the provided extension object.
	 *
	 * @param   Module  $extension  The module extension object containing the criteria for removal.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function removeModuleInstances(Module $extension): void
	{
		/**
		 * @var DatabaseDriver $db
		 * @var DatabaseQuery  $query
		 */
		$db        = $this->getDatabase();
		$table     = new \Joomla\CMS\Table\Module($db, Factory::getApplication()->getDispatcher());
		$element   = $extension->element;
		$client_id = $extension->client_id;
		$query     = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select($db->quoteName('id'))
			->from($db->quoteName('#__modules'))
			->where(
				[
					$db->quoteName('module') . ' = :element',
					$db->quoteName('client_id') . ' = :client_id',
				]
			)
			->bind(':element', $element, ParameterType::STRING)
			->bind(':client_id', $client_id, ParameterType::INTEGER);

		$moduleIds = $db->setQuery($query)->loadColumn();

		foreach ($moduleIds as $id)
		{
			$table->delete($id);
		}
	}

	/**
	 * Removes all field values for fields associated with a specific component.
	 *
	 * @param   Component  $extension  The component whose field values need to be removed.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function removeComponentFieldValues(Component $extension): void
	{
		$db = $this->getDatabase();

		/**
		 * @var DatabaseQuery $query
		 * @var DatabaseQuery $subQuery
		 */
		$query    = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$subQuery = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);

		$subQuery->select('DISTINCT ' . $db->quoteName('id'))
			->from($db->quoteName('#__fields'))
			->where($db->quoteName('context') . ' LIKE ' . $db->quote($extension->element . '.%'));

		$query->delete($db->quoteName('#__fields'))
			->where($db->quoteName('id') . ' IN (' . $subQuery . ')');

		$db->setQuery($query)->execute();
	}

	/**
	 * Removes all fields associated with the given component.
	 *
	 * @param   Component  $extension  The component whose fields need to be removed.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function removeComponentFields(Component $extension): void
	{
		$db    = $this->getDatabase();
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('DISTINCT ' . $db->quoteName('id'))
			->from($db->quoteName('#__fields'))
			->where($db->quoteName('context') . ' LIKE ' . $db->quote($extension->element . '.%'));

		/**
		 * @var  CMSApplication $app
		 * @var  MVCFactory     $factory
		 * @var  FieldTable     $table
		 */
		$app      = Factory::getApplication();
		$factory  = $app->bootComponent('com_fields')->getMVCFactory();
		$table    = $factory->createTable('Field', 'Administrator');
		$fieldIds = $db->setQuery($query)->loadColumn() ?: [];

		foreach ($fieldIds as $fieldId)
		{
			$table->delete($fieldId);
		}
	}

	/**
	 * Removes all field groups associated with the given component.
	 *
	 * @param   Component  $extension  The component whose field groups need to be removed.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function removeComponentFieldGroups(Component $extension): void
	{
		$db    = $this->getDatabase();
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('DISTINCT ' . $db->quoteName('id'))
			->from($db->quoteName('#__fields_groups'))
			->where($db->quoteName('context') . ' LIKE ' . $db->quote($extension->element . '.%'));

		/**
		 * @var  CMSApplication $app
		 * @var  MVCFactory     $factory
		 * @var  GroupTable     $table
		 */
		$app      = Factory::getApplication();
		$factory  = $app->bootComponent('com_fields')->getMVCFactory();
		$table    = $factory->createTable('Group', 'Administrator');
		$groupIDs = $db->setQuery($query)->loadColumn() ?: [];

		foreach ($groupIDs as $fieldId)
		{
			$table->delete($fieldId);
		}
	}

	/**
	 * Removes all categories associated with the specified component extension.
	 *
	 * @param   Component  $extension  The component extension whose categories need to be removed.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function removeComponentCategories(Component $extension): void
	{
		/** @var DatabaseDriver $db */
		$db      = $this->getDatabase();
		$table   = new \Joomla\CMS\Table\Category($db, Factory::getApplication()->getDispatcher());
		$element = $extension->element;
		$query   = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('DISTINCT ' . $db->quoteName('id'))
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' = :element')
			->order($db->quoteName('lft') . ' DESC')
			->bind(':element', $element, ParameterType::STRING);

		$categoryIDs = $db->setQuery($query)->loadColumn();

		foreach ($categoryIDs as $id)
		{
			$table->delete($id);
		}
	}

	/**
	 * Removes all categories associated with the specified component extension.
	 *
	 * @param   Component  $extension  The component extension whose categories need to be removed.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function removeComponentMenuRecords(Component $extension): void
	{
		/** @var DatabaseDriver $db */
		$db    = $this->getDatabase();
		$table = new \Joomla\CMS\Table\Menu($db, Factory::getApplication()->getDispatcher());
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('DISTINCT ' . $db->quoteName('id'))
			->from($db->quoteName('#__menu'))
			->where(
				[
					$db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=' . $extension->element . '&%'),
					$db->quoteName('link') . ' = ' . $db->quote('index.php?option=' . $extension->element),
				], 'OR'
			)
			->order($db->quoteName('lft') . ' DESC');

		$menuItemsIDs = $db->setQuery($query)->loadColumn();

		foreach ($menuItemsIDs as $id)
		{
			$table->delete($id);
		}
	}

	/**
	 * Removes the record of the specified extension from the database.
	 *
	 * @param   ExtensionInterface  $extension  The extension whose record is to be removed.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function removeExtensionRecord(ExtensionInterface $extension): void
	{
		/**
		 * @var DatabaseDriver $db
		 * @var DatabaseQuery  $query
		 */
		$db    = $this->getDatabase();
		$table = new \Joomla\CMS\Table\Extension($db, Factory::getApplication()->getDispatcher());

		$table->delete($extension->extension_id);
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

	/**
	 * Removes the asset record associated with a given component.
	 *
	 * @param   mixed  $extension  The component extension object.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function removeComponentAsset(Component $extension): void
	{
		/** @var DatabaseDriver $db */
		$db      = $this->getDatabase();
		$table   = new \Joomla\CMS\Table\Asset($db, Factory::getApplication()->getDispatcher());
		$element = $extension->element;

		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . ' = :element')
			->bind(':element', $element, ParameterType::STRING);

		$assetId = $db->setQuery($query)->loadResult() ?: 0;

		if ($assetId > 0)
		{
			$table->delete($assetId);
		}
	}
}