<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Helper;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\Extension;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Package;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

/**
 * Helper class to aid with orphaned extensions adoption
 *
 * @since   1.0.0
 */
abstract class AdoptionHelper
{
	/**
	 * Maps extension IDs to their **canonical** package extension.
	 *
	 * @var   array<Package>
	 * @since 1.0.0
	 */
	private static array $extensionToPackage;

	/**
	 * Extension IDs assigned a different package ID than the package claiming them as their own.
	 *
	 * @var   array<int>
	 * @since 1.0.0
	 */
	private static array $extensionsWithWrongPackage;

	/**
	 * Which package does the extension belong to?
	 *
	 * @param   int  $extensionId  The extension ID to check.
	 *
	 * @return  Package|null  The canonical package of the extension. NULL if none is found.
	 * @since   1.0.0
	 */
	public static function whichPackage(int $extensionId): ?Package
	{
		self::populate();

		return self::$extensionToPackage[$extensionId] ?? null;
	}

	/**
	 * Does the extension have the wrong package ID?
	 *
	 * This only returns true if a package claims the extension, but the extension's package_id is different to the
	 * package's extension_id.
	 *
	 * This will NOT catch the case where no package claims the extension, and the extension has a non-zero package ID.
	 *
	 * @param   int  $extensionId  The extension ID to check.
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public static function hasWrongPackageId(int $extensionId): bool
	{
		self::populate();

		return in_array($extensionId, self::$extensionsWithWrongPackage, true);
	}

	/**
	 * Initialises the information required by this helper.
	 *
	 * This iterates all known package extensions, reads their manifests, and populates this helper's internal arrays
	 * from the extensions read from the package.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private static function populate(): void
	{
		if (isset(self::$extensionToPackage) && isset(self::$extensionsWithWrongPackage))
		{
			return;
		}

		self::$extensionToPackage         = [];
		self::$extensionsWithWrongPackage = [];

		$packageIDs = PackageIDsHelper::get();

		foreach ($packageIDs as $packageID)
		{
			$package = self::getExtensionById($packageID);

			if (!$package instanceof Package)
			{
				continue;
			}

			foreach ($package->getSubextensionObjects() as $extension)
			{
				self::$extensionToPackage[$extension->extension_id] = $package;

				if (!empty($extension->package_id ?? 0) && $extension->package_id != $package->extension_id)
				{
					self::$extensionsWithWrongPackage[] = $extension->extension_id;
				}
			}
		}
	}

	/**
	 * Get an extension object given the extension ID.
	 *
	 * @param   int  $id  The extension ID
	 *
	 * @return  ExtensionInterface|null  Extension object, NULL if not found.
	 * @since   1.0.0
	 */
	private static function getExtensionById(int $id): ?ExtensionInterface
	{
		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('extension_id') . '= :extension_id')
			->bind(':extension_id', $id, ParameterType::INTEGER);

		$extData = $db->setQuery($query)->loadObject();

		if (empty($extData))
		{
			return null;
		}

		return Extension::make($extData);
	}

}