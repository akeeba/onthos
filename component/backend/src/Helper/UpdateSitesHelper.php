<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Helper;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;

/**
 * Helper class to get the extension IDs with known update sites (even disabled ones)
 *
 * @since   1.0.0
 */
abstract class UpdateSitesHelper
{
	/**
	 * The extension IDs which have an update site, even a disabled one.
	 *
	 * @var   array<int>
	 * @since 1.0.0
	 */
	private static array $extensionIDsWithUpdateSites = [];

	/**
	 * Returns the extension IDs which have an update site, even a disabled one.
	 *
	 * @return  int[]
	 * @since   1.0.0
	 */
	public static function get(): array
	{
		self::populate();

		return self::$extensionIDsWithUpdateSites;
	}

	/**
	 * Populates the array of extension IDs with known update sites if necessary.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private static function populate(): void
	{
		if (!empty(self::$extensionIDsWithUpdateSites))
		{
			return;
		}

		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query->select($db->quoteName('extension_id'))->from($db->quoteName('#__update_sites_extensions'));

		try
		{
			self::$extensionIDsWithUpdateSites = array_unique(
				$db->setQuery($query)->loadColumn() ?: []
			);
		}
		catch (\Exception $e)
		{
			self::$extensionIDsWithUpdateSites = [];
		}
	}
}