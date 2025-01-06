<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin\FilesystemOperationsTrait;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin\LanguageHandlingTrait;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin\TablesHandlingTrait;
use Akeeba\Component\Onthos\Administrator\Library\Issues\IssueManager;
use InvalidArgumentException;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;
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
 * @property-read Registry    $manifest_cache
 * @property-read Registry    $params
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
	use TablesHandlingTrait;
	use LanguageHandlingTrait;

	/**
	 * The internal cache of created extension objects.
	 *
	 * @var   array<self>
	 * @since 1.0.0
	 */
	private static array $createdObjects = [];

	/**
	 * All extension IDs with a `#__schemas` entry.
	 *
	 * @var   array<int>
	 * @since 1.0.0
	 */
	private static array $allSchemasExtensions;

	/**
	 * All schema errors across all extensions detected by Joomla.
	 *
	 * @var   array<array>
	 * @since 1.0.0
	 */
	private static array $schemaErrors;

	/**
	 * All update sites known to Joomla!.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private static array $allUpdateSites;

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
	 * The Issue Manager for this extension.
	 *
	 * @var   IssueManager
	 * @since 1.0.0
	 */
	private IssueManager $issueManager;

	/**
	 * The update servers of the extension defined in its XML manifest.
	 *
	 * @var   array<string>
	 * @since 1.0.0
	 */
	private array $canonicalUpdateServers = [];

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function __construct(object $extensionRow)
	{
		$this->extensionRow                 = $extensionRow;
		$this->extensionRow->params         = new Registry($this->extensionRow->params ?? '{}');
		$this->extensionRow->manifest_cache = new Registry($this->extensionRow->manifest_cache ?? '{}');

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
		$signature = md5(json_encode($extensionRow));

		if (isset(self::$createdObjects[$signature]))
		{
			return self::$createdObjects[$signature];
		}

		$type = $extensionRow->type ?? null;
		$type = is_string($type) ? $type : 'invalid';
		$type = (new InputFilter())->clean($type, 'cmd');
		$type = $type ?: 'invalid';

		$className = __NAMESPACE__ . '\\' . ucfirst($type);

		if (!class_exists($className))
		{
			throw new InvalidArgumentException(sprintf('Extension type %s is not supported', $type));
		}

		return self::$createdObjects[$signature] = new $className($extensionRow);
	}

	/**
	 * @inheritdoc
	 * @since   1.0.0
	 */
	public function getUpdateSites(): array
	{
		$this->populateAllUpdateSites();

		return self::$allUpdateSites[$this->extension_id] ?? [];
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	final public function __get(string $name)
	{
		if ($name === 'issues')
		{
			return $this->getIssueManager();
		}

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
			return $this->state == 0;
		}

		foreach ($this->directories as $directory)
		{
			if (!$this->fileReallyExists(JPATH_ROOT . '/' . $directory))
			{
				return false;
			}
		}

		foreach ($this->files as $file)
		{
			if (!$this->fileReallyExists(JPATH_ROOT . '/' . $file))
			{
				return false;
			}
		}

		return true;
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
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function isCore(): bool
	{
		return ExtensionHelper::checkIfCoreExtension($this->type, $this->element, $this->client_id, $this->folder);
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getParentPackage(): ?Package
	{
		if ($this->type == 'package' || empty($this->package_id ?? 0))
		{
			return null;
		}

		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');
		/** @var DatabaseQuery $query */
		$query     = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$packageId = $this->package_id;
		$query->select('*')->from($db->quoteName('#__extensions'))->where(
			$db->quoteName('extension_id') . ' = :extension_id'
		)->bind(':extension_id', $packageId, ParameterType::INTEGER);

		$extensionInfo = $db->setQuery($query)->loadObject() ?: null;

		if ($extensionInfo?->type !== 'package')
		{
			return null;
		}

		$extension = self::make($extensionInfo);

		if (!$extension instanceof Package)
		{
			// You should never be here. We already checked the extension type.
			if (defined('JDEBUG') && JDEBUG)
			{
				throw new \LogicException(
					'Expected Package extension, got ' . ($extension === null ? 'NULL' : get_class($extension))
				);
			}

			return null;
		}

		return $extension;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function getIssueManager(): IssueManager
	{
		// This ensures late initialisation for performance reasons
		$this->issueManager = $this->issueManager ?? IssueManager::make($this);

		return $this->issueManager;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function hasSchemasEntry(): bool
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

		return in_array((int) $this->extension_id, self::$allSchemasExtensions, true);
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function getSchemasErrors(): array
	{
		if (isset(self::$schemaErrors))
		{
			return self::$schemaErrors;
		}

		/**
		 * @var MVCFactoryInterface $mvcFactory
		 * @var DatabaseModel       $model
		 */
		$mvcFactory = Factory::getApplication()->bootComponent('com_installer')->getMVCFactory();
		$model      = $mvcFactory->createModel('Database', 'Administrator', ['ignore_request' => true]);

		return self::$schemaErrors = @$model->getItems();
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	final public function getCanonicalUpdateServers(): array
	{
		return $this->canonicalUpdateServers;
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function setFieldName(string $name, int|string $value): bool
	{
		$knownKeys = array_keys(get_object_vars($this->extensionRow));

		if (!in_array($name, $knownKeys, true))
		{
			throw new RuntimeException(Text::_('COM_ONTHOS_ITEM_ERR_INVALID_FIELD'));
		}

		// Set the value
		$this->extensionRow->{$name} = $value;

		// Save and return
		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');

		return $db->updateObject('#__extensions', $this->extensionRow, 'extension_id');
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

		// Extension important directories, autodetected
		$this->populateDefaultExtensionPaths();

		// Default language files
		$this->populateDefaultLanguages();

		// Default media path
		$this->populateMediaPathsFromDefault();

		// Use the default SQL files to populate the tables
		$this->populateTablesFromDefaultDirectory();

		// Discover the manifest
		$this->manifestPath = $this->getManifestXMLPath();

		try
		{
			$xml = InstallerHelper::getInstallationXML($this->element, $this->type, $this->client_id, $this->folder);

			if (!$xml instanceof SimpleXMLElement)
			{
				throw new RuntimeException('XML parsing failed');
			}

			$this->manifestPath = $this->rebaseToRoot($this->manifestPath);

			if (strtolower($this->getXMLAttribute($xml, 'type')) !== strtolower($this->type))
			{
				throw new RuntimeException('Invalid XML manifest');
			}
		}
		catch (\Throwable)
		{
			$this->languageFiles = $this->filterDirectoriesArray($this->languageFiles, true);
			$this->mediaPaths    = $this->filterDirectoriesArray($this->mediaPaths, true);

			return;
		}

		// Extension-specific hook
		$this->onAfterManifestFound($xml);

		// Extension important directories, from the manifest (certain extension types only)
		$this->populateExtensionPathsFromManifest($xml);
		$this->directories = array_map([$this, 'rebaseToRoot'], $this->directories);
		$this->files       = array_map([$this, 'rebaseToRoot'], $this->files);

		// Language files from the manifest
		$this->populateLanguagesFromManifest($xml);

		$this->languageFiles = array_map([$this, 'rebaseToRoot'], $this->languageFiles);
		$this->languageFiles = array_unique($this->languageFiles);

		// Media directories from the manifest
		$this->populateMediaDirectoriesFromManifest($xml);

		$this->mediaPaths = array_map([$this, 'rebaseToRoot'], $this->mediaPaths);
		$this->mediaPaths = array_unique($this->mediaPaths);

		// Script file from the manifest
		$this->scriptPath = $this->getScriptPathFromManifest($xml);

		// Populate the update servers
		$this->populateUpdateServersFromXMLManifest($xml);

		// Populate the tables from the manifest
		$this->populateTablesFromManifest($xml);
	}

	/**
	 * Populates the paths which determine whether the extension is installed.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	abstract protected function populateDefaultExtensionPaths(): void;

	/**
	 * Populates the default language file paths.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	abstract protected function populateDefaultLanguages(): void;

	/**
	 * Populates language file paths by reading the manifest information.
	 *
	 * @param   SimpleXMLElement  $xml
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	abstract protected function populateLanguagesFromManifest(SimpleXMLElement $xml): void;

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
	final protected function populateMediaDirectoriesFromManifest(SimpleXMLElement $xml): void
	{
		$this->mediaPaths = [];

		foreach ($xml->xpath('/extension/media') as $node)
		{
			$destination = $this->getXMLAttribute($node, 'destination', $this->element);

			$this->mediaPaths[] = sprintf("%s/media/%s", JPATH_ROOT, $destination);
		}
	}

	/**
	 * Retrieves the slug for the extension based on its type and other relevant properties.
	 *
	 * @return  string  The generated extension slug.
	 * @since   1.0.0
	 */
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

	/**
	 * Populates the media paths from default values based on the extension type and slug.
	 *
	 * This method sets the media paths property for the current object, constructing paths
	 * dependent on whether the extension is a template or another type. For templates, it
	 * handles both modern and legacy paths considering different client IDs.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	final protected function populateMediaPathsFromDefault(): void
	{
		$this->mediaPaths = [
			sprintf("%s/media/%s", JPATH_ROOT, $this->getExtensionSlug()),
		];

		// Templates are... a bit weird.
		if ($this->type == 'template')
		{
			$templateBasePath = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;
			$clientPath       = [0 => 'site', 1 => 'administrator'][$this->client_id] ?? 'invalid';
			$bareName         = str_starts_with($this->element, 'tpl_') ? substr($this->element, 4) : $this->element;

			$this->mediaPaths = [
				// Modern path
				JPATH_ROOT . '/media/' . $clientPath . '/' . $bareName,
				// Legacy path, coincides with installation path which makes things VERY confusing!
				JPATH_BASE == $templateBasePath ? (JPATH_THEMES . '/' . $bareName)
					: ($templateBasePath . '/templates/' . $bareName),
			];
		}
	}

	/**
	 * Populates the paths determining if the extension is installed from the XML manifest.
	 *
	 * This only applies to certain extension types, e.g. file extensions. The default is to do nothing, but it can be
	 * overridden in children classes.
	 *
	 * @param   SimpleXMLElement  $xml
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	protected function populateExtensionPathsFromManifest(SimpleXMLElement $xml): void
	{
		// Nothing by default
	}

	/**
	 * This method is called after the manifest is found and parsed.
	 *
	 * This is meant to be used by different extension types to retrieve and cache extension type-specific information
	 * from the XML manifest.
	 *
	 * @param   SimpleXMLElement  $xml  The SimpleXMLElement object representing the manifest.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	protected function onAfterManifestFound(SimpleXMLElement $xml)
	{
		// Nothing by default
	}

	/**
	 * Populates the update servers from the XML manifest
	 *
	 * @param   SimpleXMLElement  $xml  The SimpleXMLElement object representing the manifest.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	final protected function populateUpdateServersFromXMLManifest(SimpleXMLElement $xml): void
	{
		$this->canonicalUpdateServers = [];

		$updateServerNodes = $xml->xpath('/extension/updateservers/server');

		if (empty($updateServerNodes))
		{
			return;
		}

		foreach ($updateServerNodes as $updateServerNode)
		{
			$this->canonicalUpdateServers[] = trim((string) $updateServerNode);
		}

		$this->canonicalUpdateServers = array_unique($this->canonicalUpdateServers);
	}

	/**
	 * Add one of the alternative language files to the list of wanted language files.
	 *
	 * This method will check if any of the files it is given exist. If none of them exists, all will be added. If only
	 * some (but at least one) of them exists, then only the existing ones will be added.
	 *
	 * The idea is that you may have a language file either in your extension, or in the relevant Joomla's language
	 * folder. If AT LEAST ONE of these files exists you're fine. If we added both alternative locations it is possible
	 * that one (usually the one in the extension-specific folder) will be missing, which would be making us falsely
	 * report missing lang files.
	 *
	 * @param   string  ...$files
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	final protected function addAlternativeLanguageFiles(string $client, string ...$files): void
	{
		// Automatically remove language files for not installed languages (these files are NOT copied on installation)
		$languages = $this->getKnownLanguagesByClient($client);
		$files     = array_filter(
			$files,
			fn($file) => array_reduce(
				$languages,
				fn(bool $carry, string $language) => $carry || str_contains($file, '/' . $language . '/'),
				false
			)
		);

		$existingFiles = array_filter($files, fn($file) => @is_file($file));

		if (empty($existingFiles))
		{
			$this->languageFiles = array_merge($this->languageFiles, $files);

			return;
		}

		$this->languageFiles = array_merge($this->languageFiles, $existingFiles);
	}

	/**
	 * Populates the cache of update sites
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function populateAllUpdateSites(): void
	{
		if (isset(self::$allUpdateSites))
		{
			return;
		}

		self::$allUpdateSites = [];

		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query->select(
			[
				$db->quoteName('e.extension_id'),
				$db->quoteName('s.update_site_id'),
				$db->quoteName('s.type'),
				$db->quoteName('s.location'),
				$db->quoteName('s.enabled'),
			]
		)
			->from($db->quoteName('#__update_sites_extensions', 'e'))
			->leftJoin(
				$db->quoteName('#__update_sites', 's'),
				$db->quoteName('e.update_site_id') . ' = ' . $db->quoteName('s.update_site_id')
			)
			->where(
				[
					$db->quoteName('s.update_site_id') . ' IS NOT NULL',
					$db->quoteName('s.location') . ' IS NOT NULL',
					$db->quoteName('s.location') . ' != ' . $db->quote(''),
				]
			);

		try
		{
			$rows = $db->setQuery($query)->loadObjectList();
		}
		catch (\Exception $e)
		{
			return;
		}

		foreach ($rows as $row)
		{
			self::$allUpdateSites[$row->extension_id]   ??= [];
			self::$allUpdateSites[$row->extension_id][] = $row;
		}
	}
}