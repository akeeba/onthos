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
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Test for missing database tables.
 *
 * Only if the extension defines tables, and there is no `#__schemas` entry. One or more tables are missing.
 *
 * @since   1.0.0
 */
class MissingTables extends AbstractIssue
{
	/**
	 * All database tables known to Joomla!
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	private static array $allTables;

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
		return 'issues/reinstall';
	}

	/**
	 * @inheritDoc
	 * @since  1.0.0
	 */
	protected function doTest(): bool
	{
		// If the extension has a `#__schemas` entry the SchemaOutOfDate issue is more appropriate.
		if ($this->extension->hasSchemasEntry())
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
}