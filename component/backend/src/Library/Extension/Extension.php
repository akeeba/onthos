<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin\FilesystemOperationsTrait;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin\LanguageHandlingTrait;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin\TablesHandingTrait;
use InvalidArgumentException;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;
use Joomla\Filter\InputFilter;
use SimpleXMLElement;

defined('_JEXEC') || die;

/**
 * @property-read int         $extension_id
 * @property-read int         $package_id
 * @property-read string      $name
 * @property-read string      $type
 * @property-read string      $element
 * @property-read null|string $changelogurl
 * @property-read null|string $folder
 * @property-read int         $client_id
 * @property-read int         $enabled
 * @property-read int         $access
 * @property-read int         $protected
 * @property-read int         $locked
 * @property-read null|string $manifest_cache
 * @property-read null|string $params
 * @property-read null|int    $checked_out
 * @property-read null|string $checked_out_time
 * @property-read int         $ordering
 * @property-read int         $state
 * @property-read null|string $note
 * @property-read null|string $custom_data
 *
 * @since  1.0.0
 */
abstract class Extension implements ExtensionInterface
{
	use FilesystemOperationsTrait;
	use TablesHandingTrait;
	use LanguageHandlingTrait;

	/**
	 * The `#__extensions` table row object
	 *
	 * @var   object
	 * @since 1.0.0
	 */
	protected object $extensionRow;

	/**
	 * Possible media paths
	 *
	 * @var   array|null
	 * @since 1.0.0
	 */
	protected ?array $mediaPaths = null;

	/**
	 * Possible files which need checking.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	protected array $files = [];

	/**
	 * Possible directories which need checking.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	protected array $directories = [];

	/**
	 * Path to the manifest XML file. NULL if it's not present.
	 *
	 * @var   string|null
	 * @since 1.0.0
	 */
	protected ?string $manifestPath = null;

	/**
	 * Path to the script file. NULL if it's not present.
	 *
	 * @var   string|null
	 * @since 1.0.0
	 */
	protected ?string $scriptPath = null;

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function __construct(object $extensionRow)
	{
		$this->extensionRow = $extensionRow;

		$this->init();
	}

	/**
	 * Factory method
	 *
	 * @param   object  $extensionRow  The `#__extensions` table row.
	 *
	 * @return  ExtensionInterface
	 * @since   1.0.0
	 */
	final public static function make(object $extensionRow): ExtensionInterface
	{
		$type = $extensionRow->type ?? null;
		$type = is_string($type) ? $type : 'invalid';
		$type = (new InputFilter())->clean($type, 'cmd');
		$type = $type ?: 'invalid';

		$className = __NAMESPACE__ . '\\' . ucfirst($type);

		if (!class_exists($className))
		{
			throw new InvalidArgumentException(sprintf('Extension type %s is not supported', $type));
		}

		return new $className($extensionRow);
	}

	final public function __get(string $name)
	{
		return $this->extensionRow->{$name} ?? null;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getFiles(): array
	{
		return $this->files;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getDirectories(): array
	{
		return $this->directories;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getManifestPath(): ?string
	{
		return $this->manifestPath;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getScriptPath(): ?string
	{
		return $this->scriptPath;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getMediaPaths(): ?array
	{
		return $this->mediaPaths;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function isInstalled(): bool
	{
		// Packages are meta-extensions. If they are in the database, they are installed.
		if ($this->type == 'package')
		{
			return true;
		}

		foreach ($this->directories as $directory)
		{
			if (@is_dir(JPATH_ROOT . '/' . $directory))
			{
				return true;
			}
		}

		foreach ($this->files as $file)
		{
			if (@is_file(JPATH_ROOT . '/' . $file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function isOrphan(): bool
	{
		// Packages can never be orphans
		if ($this->type == 'package')
		{
			return false;
		}

		// TODO Check for update site

		return empty($this->package_id ?? null) && !$this->locked;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function isDiscovered(): bool
	{
		return $this->state == -1;
	}

	/**
	 * Initialise the internal variables. Called from __construct().
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	final protected function init(): void
	{
		if (empty($this->element ?? null))
		{
			return;
		}

		// Extension important directories: backend, frontend, and API paths
		$this->populateExtensionImportantPaths();

		// Default language files
		$this->populateDefaultLanguageFiles();
		$this->languageFiles = $this->filterFilesArray($this->languageFiles, true);

		// Default media path
		$this->populateMediaPathsFromDefault();

		// Use the default SQL files to populate the tables
		$this->populateTablesFromDefaultDirectory();

		// Discover the manifest
		$this->manifestPath = $this->getManifestXMLPath();

		try
		{
			$xml = InstallerHelper::getInstallationXML($this->element, $this->type, $this->client_id, $this->folder);
		}
		catch (\Throwable $e)
		{
			$xml = null;
		}

		if (!$xml instanceof SimpleXMLElement)
		{
			return;
		}

		$this->manifestPath = $this->rebaseToRoot($this->manifestPath);

		if (strtolower($this->getXMLAttribute($xml, 'type')) !== strtolower($this->type))
		{
			return;
		}

		// Language files from the manifest
		$this->addLanguagesFromManifest($xml);

		// Media directory from the manifest
		$this->addMediaDirectoriesFromManifest($xml);

		// Script file from the manifest
		$this->scriptPath = $this->getScriptPathFromManifest($xml);

		// Populate the tables from the manifest
		$this->populateTablesFromManifest($xml);
	}

	/**
	 * Populates the paths which determine whether the extension is installed.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	abstract protected function populateExtensionImportantPaths(): void;

	/**
	 * Populates the default language file paths.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	abstract protected function populateDefaultLanguageFiles(): void;

	/**
	 * Populates language file paths by reading the manifest information.
	 *
	 * @param   SimpleXMLElement  $xml
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	abstract protected function addLanguagesFromManifest(SimpleXMLElement $xml): void;

	/**
	 * Returns the installation script path read from the XML manifest.
	 *
	 * @param   SimpleXMLElement  $xml
	 *
	 * @return  string|null
	 * @since   1.0.0
	 */
	abstract protected function getScriptPathFromManifest(SimpleXMLElement $xml);

	/**
	 * Get the value of a named attribute of an XML node.
	 *
	 * This is used when parsing the XML manifests.
	 *
	 * @param   SimpleXMLElement  $node     The XML node, as a SimpleXMLElement.
	 * @param   string            $name     The name of the attribute to retrieve the value of.
	 * @param   string|null       $default  The default value to return if the attribute is missing.
	 *
	 * @return  string|null  The attribute value.
	 * @since   1.0.0
	 */
	protected function getXMLAttribute(SimpleXMLElement $node, string $name, string $default = null): ?string
	{
		$attributes = $node->attributes();

		if (isset($attributes[$name]))
		{
			return (string) $attributes[$name];
		}

		return $default;
	}

	/**
	 * Get the absolute filesystem path to the XML manifest file of the extension.
	 *
	 * Based on Joomla's InstallerHelper::getInstallationXML() method. We can't use that method directly because it
	 * returns a SimpleXMLElement, not the path to the file itself. We need the actual path for clean-up purposes.
	 *
	 * @return  string|null  NULL if the manifest is not found.
	 * @since   1.0.0
	 * @see     \Joomla\Component\Installer\Administrator\Helper\InstallerHelper::getInstallationXML
	 */
	final protected function getManifestXMLPath(): ?string
	{
		$path = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR, 3 => JPATH_API][$this->client_id ?? 0] ?? JPATH_SITE;

		switch ($this->type)
		{
			case 'component':
				$path .= '/components/' . $this->element . '/' . substr($this->element, 4) . '.xml';
				break;
			case 'plugin':
				$path .= '/plugins/' . $this->folder . '/' . $this->element . '/' . $this->element . '.xml';
				break;
			case 'module':
				$path .= '/modules/' . $this->element . '/' . $this->element . '.xml';
				break;
			case 'template':
				$path .= '/templates/' . $this->element . '/templateDetails.xml';
				break;
			case 'library':
				$path = JPATH_ADMINISTRATOR . '/manifests/libraries/' . $this->element . '.xml';
				break;
			case 'file':
				$path = JPATH_ADMINISTRATOR . '/manifests/files/' . $this->element . '.xml';
				break;
			case 'package':
				$path = JPATH_ADMINISTRATOR . '/manifests/packages/' . $this->element . '.xml';
				break;
			case 'language':
				$path .= '/language/' . $this->element . '/install.xml';
				break;
		}
		if (file_exists($path) === false)
		{
			return null;
		}

		return $path;

	}

	/**
	 * Adds media directories based on what is read from the XML manifest.
	 *
	 * @param   SimpleXMLElement  $xml
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	final protected function addMediaDirectoriesFromManifest(SimpleXMLElement $xml): void
	{
		// Reasoning: the manifest exists and is the authoritative resource of media paths.
		$this->mediaPaths = [];

		foreach ($xml->xpath('/extension/media') as $node)
		{
			$destination = $this->getXMLAttribute($node, 'destination', $this->element);
			$folder      = $this->getXMLAttribute($node, 'folder', 'media');

			$this->mediaPaths[] = sprintf("%s/%s/%s", JPATH_ROOT, $folder, $destination);
		}

		$this->mediaPaths = $this->filterDirectoriesArray($this->mediaPaths, true);
	}

	final protected function getExtensionSlug(): string
	{
		switch ($this->type)
		{
			case 'file':
				return 'files_' . $this->element;

			case 'library':
				$parts  = explode('/', $this->element);
				$vendor = (isset($parts[1]) ? $parts[0] : null);

				return 'lib_' . ($vendor ? implode('_', $parts) : $this->element);

			case 'plugin':
				return 'plg_' . $this->folder . '_' . $this->element;

			case 'template':
				return 'tpl_' . $this->element;

			default:
				return $this->element;
		}
	}

	final protected function populateMediaPathsFromDefault()
	{
		$mediaPaths = [
			sprintf("%s/media/%s", JPATH_ROOT, $this->getExtensionSlug()),
		];

		// Templates are... a bit weird.
		if ($this->type == 'template')
		{
			$templateBasePath = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;
			$clientPath       = [0 => 'site', 1 => 'administrator'][$this->client_id] ?? 'invalid';
			$bareName         = str_starts_with($this->element, 'tpl_') ? substr($this->element, 4) : $this->element;

			$mediaPaths = [
				// Modern path
				JPATH_ROOT . '/media/' . $clientPath . '/' . $bareName,
				// Legacy path, coincides with installation path which makes things VERY confusing!
				JPATH_BASE == $templateBasePath ? (JPATH_THEMES . '/' . $bareName)
					: ($templateBasePath . '/templates/' . $bareName),
			];
		}

		$this->mediaPaths = $this->filterDirectoriesArray($mediaPaths, true);
	}
}