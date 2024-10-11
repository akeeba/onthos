<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin;

defined('_JEXEC') || die;

use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\Folder;
use PHPSQLParser\PHPSQLParser;
use SimpleXMLElement;

/**
 * Trait providing functionality to handle tables declared in Joomla extensions.
 *
 * @since  1.0.0
 */
trait TablesHandingTrait
{
	/**
	 * Possible tables which need checking.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	protected array $tables = [];

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getTables(): array
	{
		return $this->tables;
	}

	/**
	 * Populates the extension tables using the Joomla! hardcoded default `sql` directory.
	 *
	 * This DOES NOT read the manifest. It assumes the extension has a directory named `sql` under its main extension
	 * directory (for components it's the extension's admin directory) which has .sql files inside it in some sort of
	 * directory structure. This default is hardcoded in Joomla's Database Fix code where it looks for SQL update files
	 * under the extension's `sql/updates` folder. We are being slightly more flexible here.
	 *
	 * This is meant to be a quick and dirty way to identify extension tables if the manifest is missing or corrupt. It
	 * is not meant as the only, or even preferred, method.
	 *
	 * @return  void
	 * @since   1.0.0
	 * @see     self::populateTablesFromManifest
	 */
	final protected function populateTablesFromDefaultDirectory(): void
	{
		if ($this->type === 'component')
		{
			$basePath = JPATH_ADMINISTRATOR . '/components/' . $this->element;
		}
		elseif ($this->type === 'plugin')
		{
			$basePath = JPATH_PLUGINS . '/' . $this->folder . '/' . $this->element;
		}
		elseif ($this->type === 'module')
		{
			if ($this->client_id == 1)
			{
				$basePath = JPATH_ADMINISTRATOR . '/modules/' . $this->element;
			}
			elseif ($this->client_id == 0)
			{
				$basePath = JPATH_SITE . '/modules/' . $this->element;
			}
			else
			{
				// Cannot process modules with an invalid client ID.
				return;
			}
		}
		elseif ($this->type === 'file' && $this->element === 'com_admin')
		{
			// Specific bodge for the Joomla CMS special database check which points to com_admin
			$basePath = JPATH_ADMINISTRATOR . '/components/' . $this->element;
		}
		else
		{
			// Unknown extension type, or other type (e.g. library, files etc) which don't have known SQL paths
			return;
		}

		/**
		 * The /sql subdirectory as the default schema location is a hardcoded default in Joomla.
		 *
		 * @see \Joomla\Component\Installer\Administrator\Model\DatabaseModel::fetchSchemaCache
		 */
		$sqlFiles = Folder::files($basePath . '/sql', '\.sql$', true, true) ?: [];

		foreach ($sqlFiles as $sqlFile)
		{
			$this->populateTablesFromSQLFile($sqlFile);
		}

		$this->tables = array_unique($this->tables);
	}

	/**
	 * Populates database tables from the SQL files specified in the extension's XML manifest file.
	 *
	 * This is the most accurate way to do this. Instead of using a hardcoded default, we examine the manifest to locate
	 * the installation SQL file, and the path to the update SQL files. We then read them, parse them, and identify the
	 * created tables.
	 *
	 * @param   SimpleXMLElement  $xml  The XML manifest.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	final protected function populateTablesFromManifest(SimpleXMLElement $xml): void
	{
		$sqlFiles = [];
		$basePath = JPATH_ADMINISTRATOR . '/components/' . $this->element . '/';

		foreach ($xml->xpath('/extension/install/sql/file') as $fileNode)
		{
			$driver  = $this->getXMLAttribute($fileNode, 'driver', 'mysql');
			$charset = $this->getXMLAttribute($fileNode, 'charset', 'utf8');
			$relPath = (string) $fileNode;

			if ($charset != 'utf8')
			{
				continue;
			}

			if (str_starts_with($driver, 'mysql') || str_starts_with($driver, 'postgres'))
			{
				$sqlFiles[] = $basePath . ltrim($relPath, '/');
			}
		}

		foreach ($xml->xpath('/extension/update/schemas/schemapath') as $folderNode)
		{
			$type = $this->getXMLAttribute($folderNode, 'type', 'mysql');

			if (!str_starts_with($type, 'mysql') && !str_starts_with($type, 'postgres'))
			{
				continue;
			}

			$relPath = (string) $folderNode;
			$absPath = $basePath . ltrim($relPath, '/');

			if (!is_dir($absPath))
			{
				continue;
			}

			$sqlFiles = array_merge(
				$sqlFiles, Folder::files($absPath, '\.sql$', false, true) ?: []
			);
		}

		foreach ($sqlFiles as $sqlFile)
		{
			$this->populateTablesFromSQLFile($sqlFile);
		}

		$this->tables = array_unique($this->tables);
	}

	/**
	 * Populates the list of table names from an SQL file by parsing CREATE TABLE statements.
	 *
	 * @param   mixed  $sqlFile  The file path to the SQL file to be read. Must be a readable file path.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	final protected function populateTablesFromSQLFile(mixed $sqlFile): void
	{
		if (!@file_exists($sqlFile) || !@is_readable($sqlFile))
		{
			return;
		}

		$buffer = @file_get_contents($sqlFile);

		if ($buffer === false)
		{
			return;
		}

		foreach (DatabaseDriver::splitSql($buffer) as $statement)
		{
			if (!preg_match('/CREATE\s+TABLE/i', $statement))
			{
				continue;
			}

			$parser = new PHPSQLParser($statement, true);

			if (!is_array($parser->parsed) || empty($parser->parsed) || !isset($parser->parsed['TABLE']))
			{
				continue;
			}

			$rawTableName = $parser->parsed['TABLE']['name'] ?? null;

			if (!is_string($rawTableName))
			{
				continue;
			}

			$tableName = trim($rawTableName, '`"');

			$this->tables[] = $tableName;
		}
	}
}