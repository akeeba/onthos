<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Model;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\Extension;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Throwable;

class ItemsModel extends ListModel
{
	private int $total;

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

			$search = trim($this->getState('filter.search') ?: '');

			if (!empty($search))
			{
				$this->extensions = array_filter(
					$this->extensions,
					fn($extension) => stripos($extension->getName(), $search) !== false
				);
			}

			// TODO Apply filters applicable only to processed results
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
			uasort($this->extensions, fn(ExtensionInterface $a, ExtensionInterface $b) => $a->getName() <=> $b->getName());

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
	 * Get an extension object given the extension selection criteria.
	 *
	 * The $type and $element parameters are always required.
	 *
	 * Only for plugins you need to provide the $folder parameter.
	 *
	 * Only for language, module, and template extensions you need to provide $clientId. The acceptable values are 0 for
	 * the frontend, 1 for the backend, and 3 for the API application.
	 *
	 * @param   string       $type      Extension type.
	 * @param   string       $element   The `element` column in `#__extensions`.
	 * @param   string|null  $folder    Only for plugins. The plugin group.
	 * @param   int|null     $clientId  Only for some types. Application ID the extension applies to.
	 *
	 * @return  ExtensionInterface|null  Extension object, NULL if not found.
	 * @since   1.0.0
	 */
	public function getExtensionByDetails(string $type, string $element, ?string $folder = null, ?int $clientId = null
	): ?ExtensionInterface
	{
		$db = $this->getDatabase();
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . '= :type')
			->where($db->quoteName('element') . ' = :element')
			->bind(':type', $type, ParameterType::STRING)
			->bind(':element', $element, ParameterType::STRING);

		if ($type === 'plugin')
		{
			$query->where($db->quoteName('folder') . ' = :folder')
				->bind(':folder', $folder, ParameterType::STRING);
		}

		if (!empty($clientId))
		{
			$query->where($db->quoteName('client_id') . ' = :client_id')
				->bind(':client_id', $clientId, ParameterType::INTEGER);
		}

		$extData = $db->setQuery($query)->loadObject();

		if (empty($extData))
		{
			return null;
		}

		return Extension::make($extData);
	}

	/**
	 * Get an extension object given the extension ID.
	 *
	 * @param   int  $id  The extension ID
	 *
	 * @return  ExtensionInterface|null  Extension object, NULL if not found.
	 * @since   1.0.0
	 */
	public function getExtensionById(int $id): ?ExtensionInterface
	{
		$db = $this->getDatabase();
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

		// Side note: this is more elegant than Joomla's way of changing the method signature.
		$ordering  ??= 'extension_id';
		$direction ??= 'asc';

		parent::populateState($ordering, $direction);
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
		$id = $this->getState('filter.extension_id');

		if (is_int($id) && $id > 0)
		{
			$query->where($db->quoteName('extension_id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
		}

		// Filter: Package ID
		$package_id = $this->getState('filter.extension_id');

		if (is_int($package_id) && $package_id == 0)
		{
			$query->where(
				'(' .
				$db->quoteName('package_id') . ' IS NULL OR ' .
				$db->quoteName('package_id') . ' = 0'
			);
		}
		elseif (is_int($package_id))
		{
			$query->where($db->quoteName('package_id') . ' = :package_id')
				->bind(':package_id', $package_id, ParameterType::INTEGER);
		}

		// Filter: type
		$type = $this->getState('filter.type');

		if (!empty($type))
		{
			$query->where($db->quoteName('type') . ' = :type')
				->bind(':type', $type, ParameterType::STRING);
		}

		// Filter: element
		$element = $this->getState('filter.element');

		if (!empty($element))
		{
			$query->where($db->quoteName('element') . ' = :element')
				->bind(':element', $element, ParameterType::STRING);
		}

		// Filter: folder
		$folder = $this->getState('filter.folder');

		if (!empty($folder))
		{
			$query->where($db->quoteName('folder') . ' = :folder')
				->bind(':folder', $folder, ParameterType::STRING);
		}

		// Filter: client_id
		$client_id = $this->getState('filter.client_id');

		if (!empty($client_id))
		{
			$query->where($db->quoteName('client_id') . ' = :client_id')
				->bind(':client_id', $client_id, ParameterType::INTEGER);
		}

		// Filter: enabled
		$enabled = $this->getState('filter.enabled');

		if (is_integer($enabled))
		{
			$query->where($db->quoteName('enabled') . ' = :enabled')
				->bind(':enabled', $enabled, ParameterType::INTEGER);
		}

		// Filter: access
		$access = $this->getState('filter.access');

		if (!empty($access) && is_integer($access))
		{
			$query->where($db->quoteName('access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		}

		// Filter: protected
		$protected = $this->getState('filter.protected');

		if (is_integer($protected))
		{
			$query->where($db->quoteName('protected') . ' = :protected')
				->bind(':protected', $protected, ParameterType::INTEGER);
		}

		// Filter: locked
		$locked = $this->getState('filter.locked');

		if (is_integer($locked))
		{
			$query->where($db->quoteName('locked') . ' = :locked')
				->bind(':locked', $locked, ParameterType::INTEGER);
		}

		// Filter: state
		$state = $this->getState('filter.state');

		if (is_integer($state))
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