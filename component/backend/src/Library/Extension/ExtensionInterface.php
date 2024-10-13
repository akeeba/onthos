<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use Akeeba\Component\Onthos\Administrator\Library\Issues\IssueManager;
use Joomla\Registry\Registry;

defined('_JEXEC') || die;

/**
 * Interface to an extension object.
 *
 * @property-read int          $extension_id
 * @property-read int          $package_id
 * @property-read string       $name
 * @property-read string       $type
 * @property-read string       $element
 * @property-read null|string  $changelogurl
 * @property-read null|string  $folder
 * @property-read int          $client_id
 * @property-read int          $enabled
 * @property-read int          $access
 * @property-read int          $protected
 * @property-read int          $locked
 * @property-read Registry     $manifest_cache
 * @property-read Registry     $params
 * @property-read null|int     $checked_out
 * @property-read null|string  $checked_out_time
 * @property-read int          $ordering
 * @property-read int          $state
 * @property-read null|string  $note
 * @property-read null|string  $custom_data
 * @property-read IssueManager $issues
 *
 * @since 1.0.0
 */
interface ExtensionInterface
{
	/**
	 * Construct an extension object given an `#__extensions` row.
	 *
	 * @param   object  $extensionRow  The extensions table row in object format
	 *
	 * @since   1.0.0
	 */
	public function __construct(object $extensionRow);

	/**
	 * Does the extension appear to be installed?
	 *
	 * For packages, it only checks the extension state.
	 *
	 * For any other extension, it returns true if all the extension-important files or directories exist.
	 *
	 * This is not a guarantee the extension works. It is only a check confirming that the extension is not missing
	 * files. Its files may be outdated, corrupt, or there might be files / directories in subdirectories of the
	 * declared directories in the XML file which are missing or corrupt.
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function isInstalled(): bool;

	/**
	 * Is this a discovered, but not yet fully installed, extension?
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function isDiscovered(): bool;

	/**
	 * Is this a core extension?
	 *
	 * This checks against Joomla's **hardcoded** list of core extensions which is part of Joomla! Update itself. It
	 * does NOT rely on the possibly misleading `protected` database column. This is a huge difference from Joomla's
	 * com_installer.
	 *
	 * @return  bool
	 * @since   1.0.0
	 * @see     \Joomla\CMS\Extension\ExtensionHelper::checkIfCoreExtension()
	 */
	public function isCore(): bool;

	/**
	 * Get the human-readable name of the extension, if possible.
	 *
	 * @return  string|null
	 * @since   1.0.0
	 */
	public function getName(): ?string;

	/**
	 * Get possible files which indicate the extension may really exist.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function getFiles(): array;

	/**
	 * Get possible directories which indicate the extension may really exist.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function getDirectories(): array;

	/**
	 * Get the paths to possible language files.
	 *
	 * Used for clean-up purposes only.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function getLanguageFiles(): array;

	/**
	 * Get possible database tables which may have been installed by the extension.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function getTables(): array;

	/**
	 * Get the possible paths to the extension's media directory/-ies.
	 *
	 * Used for clean-up purposes only.
	 *
	 * @return  array|null
	 * @since   1.0.0
	 */
	public function getMediaPaths(): ?array;

	/**
	 * Get the path to the XML manifest file. NULL if not applicable.
	 *
	 * @return  string|null
	 * @since   1.0.0
	 */
	public function getManifestPath(): ?string;

	/**
	 * Get the path to the script file. NULL if not applicable.
	 *
	 * Used for clean-up purposes only.
	 *
	 * @return  string|null
	 * @since   1.0.0
	 */
	public function getScriptPath(): ?string;

	/**
	 * Get the parent package of the extension.
	 *
	 * @return  ExtensionInterface|null  The parent package. NULL if it doesn't exist, or is invalid.
	 * @since   1.0.0
	 */
	public function getParentPackage(): ?Package;

	/**
	 * Returns the Issue Manager for this extension.
	 *
	 * @return  IssueManager
	 * @since   1.0.0
	 */
	public function getIssueManager(): IssueManager;

	/**
	 * Magic getter.
	 *
	 * Retrieves properties from the extension object.
	 *
	 * @param   string  $name  The property to retrieve.
	 *
	 * @return  null
	 */
	public function __get(string $name);
}