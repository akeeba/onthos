<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

defined('_JEXEC') || die;

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
	 * Returns true if any of the extension-important files or directories exists.
	 *
	 * Extensions discovered, but not installed yet, will return true.
	 *
	 * Partially installed but not working extensions will ALSO return true.
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function isInstalled(): bool;

	/**
	 * Is this an orphan extension?
	 *
	 * These are extensions which do not belong to a package, are not locked, and do not have an update site.
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function isOrphan(): bool;

	/**
	 * Is this a discovered, but not yet fully installed, extension?
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function isDiscovered(): bool;

	/**
	 * Does this extension miss any language files?
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function isMissingLanguages(bool $onlySystem = false): bool;

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
	 * Get the parent package of the extension
	 *
	 * @return  ExtensionInterface|null
	 * @since   1.0.0
	 */
	public function getParentPackage(): ?ExtensionInterface;
}