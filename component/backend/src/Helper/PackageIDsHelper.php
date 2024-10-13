<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Helper;


use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;

defined('_JEXEC') || die;

/**
 * Helper class to get all known package extension IDs
 *
 * @since   1.0.0
 */
abstract class PackageIDsHelper
{
	/**
	 * The IDs of all extension packages known to Joomla!.
	 *
	 * @var   array<int>
	 * @since 1.0.0
	 */
	private static array $packageIDs = [];

	/**
	 * Returns the IDs of all package extensions known to Joomla!.
	 *
	 * @return  array<int>
	 * @since   1.0.0
	 */
	public static function get(): array
	{
		self::populate();

		return self::$packageIDs;
	}

	/**
	 * Populates the array of package extension IDs if necessary.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private static function populate(): void
	{
		if (!empty(self::$packageIDs))
		{
			return;
		}

		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query->select($db->quoteName('extension_id'))->from($db->quoteName('#__extensions'))->where(
			$db->quoteName('type') . ' = ' . $db->quote('package')
		);

		try
		{
			self::$packageIDs = array_unique(
				$db->setQuery($query)->loadColumn() ?: []
			);
		}
		catch (\Exception $e)
		{
			self::$packageIDs = [];
		}
	}

}