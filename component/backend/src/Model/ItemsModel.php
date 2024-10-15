<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Model;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin\FilesystemOperationsTrait;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Throwable;

class ItemsModel extends ListModel
{
	use GetExtensionByIdTrait;
	use UninstallTrait;
	use FilesystemOperationsTrait;

	/**
	 * The total number of available extensions to display after applying all filters.
	 *
	 * @var   int
	 * @since 1.0.0
	 */
	private int $total;

	/**
	 * The filtered extension objects.
	 *
	 * @var   array<ExtensionInterface>
	 * @since 1.0.0
	 */
	private array $extensions;

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		$config['filter_fields'] = $config['filter_fields'] ?? [];
		$config['filter_fields'] = $config['filter_fields']
			?: [
				'extension_id',
				'name',
				'package_id',
				'type',
				'element',
				'folder',
				'client_id',
				'enabled',
				'access',
				'protected',
				'locked',
				'state',
				'search',
				'isCore',
				'issues',
			];

		parent::__construct($config, $factory);
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function getItems()
	{
		if (!isset($this->extensions))
		{
			$this->extensions = $this->processAllExtensions();

			$search = trim($this->getState('filter.search', '') ?: '');

			if (!empty($search))
			{
				$this->extensions = array_filter(
					$this->extensions,
					fn($extension) => stripos($extension->getName(), $search) !== false
				);
			}

			$isCore = $this->getState('filter.isCore', '');

			if ($isCore !== '')
			{
				$this->extensions = array_filter(
					$this->extensions,
					fn($extension) => $extension->isCore() === boolval($isCore)
				);
			}

			$issues = $this->getState('filter.issues', '');

			if ($issues === '*')
			{
				$this->extensions = array_filter(
					$this->extensions,
					fn($extension) => !empty($extension->issues->getIssues())
				);
			}
			elseif ($issues !== '')
			{
				$this->extensions = array_filter(
					$this->extensions,
					fn($extension) => $extension->issues->hasIssue($issues)
				);
			}
		}

		// Update the total
		$this->total = count($this->extensions);

		// Apply ordering in some special cases where the DB ordering does not work.
		$ordering  = $this->getState('list.ordering') ?? 'extension_id';
		$direction = $this->getState('list.direction') ?? 'ASC';
		$direction = is_string($direction) ? strtoupper($direction) : 'ASC';
		$direction = in_array($direction, ['ASC', 'DESC']) ? $direction : 'ASC';

		if ($ordering === 'name')
		{
			uasort(
				$this->extensions, fn(ExtensionInterface $a, ExtensionInterface $b) => $a->getName() <=> $b->getName()
			);

			if ($direction === 'DESC')
			{
				$this->extensions = array_reverse($this->extensions);
			}
		}

		// Apply pagination and return
		$offset = min(max($this->getState('list.start'), 0), count($this->extensions) - 1);
		$length = max($this->getState('list.limit'), 5);

		return array_slice($this->extensions, $offset, $length);
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function getTotal()
	{
		if (!isset($this->total))
		{
			$this->getItems();
		}

		return $this->total;
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function getIsEmptyState(): bool
	{
		$db = $this->getDatabase();
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from($db->quoteName('#__extensions'));

		try
		{
			return $db->setQuery($query)->loadResult() ?: 0;
		}
		catch (\Throwable)
		{
			return 0;
		}
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();

		// If we're not under a web application we have nothing to do here.
		if (!$app instanceof CMSApplication)
		{
			return;
		}

		// Filter: extension_id
		$extension_id = $app->getUserStateFromRequest(
			$this->context . 'filter.extension_id', 'filter_extension_id', '', 'string'
		);
		$this->setState('filter.extension_id', $extension_id === '' ? $extension_id : intval($extension_id));

		// Filter: package_id
		$package_id = $app->getUserStateFromRequest(
			$this->context . 'filter.package_id', 'filter_package_id', '', 'string'
		);
		$this->setState('filter.package_id', $package_id === '' ? $package_id : intval($package_id));

		// Filter: type
		$type = $app->getUserStateFromRequest($this->context . 'filter.type', 'filter_type', '', 'string');
		$this->setState('filter.type', $type);

		// Filter: element
		$element = $app->getUserStateFromRequest($this->context . 'filter.element', 'filter_element', '', 'string');
		$this->setState('filter.element', $element);

		// Filter: folder
		$folder = $app->getUserStateFromRequest($this->context . 'filter.folder', 'filter_folder', '', 'string');
		$this->setState('filter.folder', $folder);

		// Filter: client_id
		$client_id = $app->getUserStateFromRequest(
			$this->context . 'filter.client_id', 'filter_client_id', '', 'string'
		);
		$this->setState('filter.client_id', $client_id === '' ? $client_id : intval($client_id));

		// Filter: enabled
		$enabled = $app->getUserStateFromRequest($this->context . 'filter.enabled', 'filter_enabled', '', 'string');
		$this->setState('filter.enabled', $enabled === '' ? $enabled : intval($enabled));

		// Filter: access
		$access = $app->getUserStateFromRequest($this->context . 'filter.access', 'filter_access', '', 'string');
		$this->setState('filter.access', $access === '' ? $access : intval($access));

		// Filter: protected
		$protected = $app->getUserStateFromRequest(
			$this->context . 'filter.protected', 'filter_protected', '', 'string'
		);
		$this->setState('filter.protected', $protected === '' ? $protected : intval($protected));

		// Filter: locked
		$locked = $app->getUserStateFromRequest($this->context . 'filter.locked', 'filter_locked', '', 'string');
		$this->setState('filter.locked', $locked === '' ? $locked : intval($locked));

		// Filter: state
		$state = $app->getUserStateFromRequest($this->context . 'filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state === '' ? $state : intval($state));

		// Filter: search
		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		// Filter: isCore
		$isCore = $app->getUserStateFromRequest($this->context . 'filter.isCore', 'filter_isCore', '', 'string');
		$this->setState('filter.isCore', $isCore === '' ? $isCore : intval($isCore));

		// Filter: issues
		$issues = $app->getUserStateFromRequest($this->context . 'filter.issues', 'filter_issues', '', 'string');
		$this->setState('filter.issues', $issues);

		// Side note: this is more elegant than Joomla's way of changing the method signature.
		$ordering  ??= 'extension_id';
		$direction ??= 'asc';

		parent::populateState($ordering, $direction);
	}

	/**
	 * Is the string provided one of the known Joomla! core directories?
	 *
	 * @param   string  $fileOrDir
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	private function isCoreJoomlaDirectory(string $fileOrDir): bool
	{
		static $root;
		static $coreDirs;

		$coreDirs ??= array_map(
			fn($x) => $this->normalisePath($x),
			array_unique(
				[
					JPATH_PUBLIC,
					JPATH_CONFIGURATION,
					JPATH_ADMINISTRATOR,
					JPATH_LIBRARIES,
					JPATH_PLUGINS,
					JPATH_THEMES,
					JPATH_CACHE,
					JPATH_MANIFESTS,
					JPATH_API,
					JPATH_CLI,
					JPATH_ROOT . '/api',
					JPATH_ROOT . '/cli',
					JPATH_ROOT . '/components',
					JPATH_ROOT . '/images',
					JPATH_ROOT . '/includes',
					JPATH_ROOT . '/language',
					JPATH_ROOT . '/layouts',
					JPATH_ROOT . '/libraries',
					JPATH_ROOT . '/media',
					JPATH_ROOT . '/modules',
					JPATH_ROOT . '/plugins',
					JPATH_ROOT . '/templates',
					JPATH_ROOT . '/tmp',
					JPATH_ADMINISTRATOR . '/cache',
					JPATH_ADMINISTRATOR . '/components',
					JPATH_ADMINISTRATOR . '/help',
					JPATH_ADMINISTRATOR . '/includes',
					JPATH_ADMINISTRATOR . '/language',
					JPATH_ADMINISTRATOR . '/logs',
					JPATH_ADMINISTRATOR . '/manifests',
					JPATH_ADMINISTRATOR . '/modules',
					JPATH_ADMINISTRATOR . '/templates',
				]
			)
		);
		$root     ??= rtrim($this->normalisePath(JPATH_ROOT) . '/');

		// Normalise the path
		$fileOrDir = rtrim($this->normalisePath($fileOrDir), '/');

		// If it does not exist, or is a file, it's not a core dir.
		if (!@file_exists($fileOrDir) || !@is_file($fileOrDir))
		{
			return false;
		}

		// If it's the site's root return immediate TRUE. This is to prevent JPATH_ROOT overshooting the next rule.
		if ($fileOrDir === $root)
		{
			return true;
		}

		// If the path is not under the site's root return immediate FALSE
		if (!str_starts_with($fileOrDir, $root . '/'))
		{
			return false;
		}

		return in_array($fileOrDir, $coreDirs);
	}

	/**
	 * Clear out the Joomla Installer's object Singleton cache.
	 *
	 * This is VERY important when uninstalling multiple extensions. We have discovered that when uninstalling multiple
	 * extensions of different types the cached installer may try to use the wrong adapter for subsequent
	 * uninstallations, causing them to fail.
	 *
	 * This was reported to Joomla! back in 2022 and is still unfixed at the time of this writing. No worries. I can
	 * summon the Dark Arts of PHP REFLECTION!
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function zapInstallerInstance(): void
	{
		(new \ReflectionClass(Installer::class))->setStaticPropertyValue('instances', []);
	}

	/**
	 * Processes all extensions in Joomla's database
	 *
	 * @return  array<ExtensionInterface>
	 * @since   1.0.0
	 */
	private function processAllExtensions(): array
	{
		$db = $this->getDatabase();
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select($query->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'));

		// Filter: Extension ID
		$id = $this->getState('filter.extension_id', '');

		if ($id !== '' && intval($id) > 0)
		{
			$query->where($db->quoteName('extension_id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
		}

		// Filter: Package ID
		$package_id = $this->getState('filter.package_id', '');

		if ($package_id === '0')
		{
			$query->where(
				'(' .
				$db->quoteName('package_id') . ' IS NULL OR ' .
				$db->quoteName('package_id') . ' = 0'
			);
		}
		elseif ($package_id !== '')
		{
			$query->where($db->quoteName('package_id') . ' = :package_id')
				->bind(':package_id', $package_id, ParameterType::INTEGER);
		}

		// Filter: type
		$type = $this->getState('filter.type', '');

		if (!empty($type))
		{
			$query->where($db->quoteName('type') . ' = :type')
				->bind(':type', $type, ParameterType::STRING);
		}

		// Filter: element
		$element = $this->getState('filter.element', '');

		if (!empty($element))
		{
			$query->where($db->quoteName('element') . ' = :element')
				->bind(':element', $element, ParameterType::STRING);
		}

		// Filter: folder
		$folder = $this->getState('filter.folder', '');

		if (!empty($folder))
		{
			$query->where($db->quoteName('folder') . ' = :folder')
				->bind(':folder', $folder, ParameterType::STRING);
		}

		// Filter: client_id
		$client_id = $this->getState('filter.client_id', '');

		if (!empty($client_id))
		{
			$query->where($db->quoteName('client_id') . ' = :client_id')
				->bind(':client_id', $client_id, ParameterType::INTEGER);
		}

		// Filter: enabled and discovered
		$enabled = $this->getState('filter.enabled', '');

		if ($enabled !== '')
		{
			$query->where($db->quoteName('enabled') . ' = :enabled')
				->bind(':enabled', $enabled, ParameterType::INTEGER);
		}

		// Filter: access
		$access = $this->getState('filter.access', '');

		if ($access !== '')
		{
			$query->where($db->quoteName('access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		}

		// Filter: protected
		$protected = $this->getState('filter.protected', '');

		if ($protected !== '')
		{
			$query->where($db->quoteName('protected') . ' = :protected')
				->bind(':protected', $protected, ParameterType::INTEGER);
		}

		// Filter: locked
		$locked = $this->getState('filter.locked', '');

		if ($locked !== '')
		{
			$query->where($db->quoteName('locked') . ' = :locked')
				->bind(':locked', $locked, ParameterType::INTEGER);
		}

		// Filter: state
		$state = $this->getState('filter.state', '');

		if ($state !== '')
		{
			$query->where($db->quoteName('state') . ' = :state')
				->bind(':state', $state, ParameterType::INTEGER);
		}

		// Apply the order
		$ordering  = $this->getState('list.ordering') ?? 'extension_id';
		$direction = $this->getState('list.direction') ?? 'ASC';
		$direction = is_string($direction) ? strtoupper($direction) : 'ASC';
		$direction = in_array($direction, ['ASC', 'DESC']) ? $direction : 'ASC';

		$query->order($db->quoteName($ordering) . ' ' . $direction);

		try
		{
			$ids = $db->setQuery($query)->loadColumn() ?: [];
		}
		catch (Throwable)
		{
			$ids = [];
		}

		return array_map([$this, 'getExtensionById'], $ids);
	}
}