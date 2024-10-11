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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

class MainModel extends BaseDatabaseModel
{
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
}