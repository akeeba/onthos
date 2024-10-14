<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Utilities\ArrayHelper;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

class MissingTables extends AbstractIssue
{
	private static array $allTables;

	private static array $allSchemasExtensions;

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
	public function getDetailsTemplate(): string
	{
		return 'commontemplates/reinstall';
	}

	/**
	 * @inheritDoc
	 */
	protected function doTest(): bool
	{
		// If the extension has a `#__schemas` entry the SchemaOutOfDate issue is more appropriate.
		if ($this->hasSchemasEntry())
		{
			return false;
		}

		// Make sure I do have some tables
		$myTables = $this->extension->getTables();

		if (empty($myTables))
		{
			return false;
		}

		// Check is all my tables are present
		/** @var DatabaseDriver $db */
		$db       = Factory::getContainer()->get('db');
		$myTables = array_map([$db, 'replacePrefix'], $myTables);

		try
		{
			self::$allTables ??= $db->getTableList();
		}
		catch (\Throwable)
		{
			self::$allTables = [];
		}

		return !empty(array_diff($myTables, self::$allTables));
	}

	/**
	 * Does this extension have a `#__schemas` entry?
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	private function hasSchemasEntry(): bool
	{
		if (!isset(self::$allSchemasExtensions))
		{
			/** @var DatabaseDriver $db */
			$db = Factory::getContainer()->get('db');
			/** @var DatabaseQuery $query */
			$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
			$query->select($db->quoteName('extension_id'))
				->from('#__schemas');

			try
			{
				self::$allSchemasExtensions = $db->setQuery($query)->loadColumn();
				self::$allSchemasExtensions = ArrayHelper::toInteger(self::$allSchemasExtensions);
			}
			catch (\Throwable)
			{
				self::$allSchemasExtensions = [];
			}
		}

		return in_array((int) $this->extension->extension_id, self::$allSchemasExtensions, true);
	}

}