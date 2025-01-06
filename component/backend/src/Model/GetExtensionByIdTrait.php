<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Model;

use Akeeba\Component\Onthos\Administrator\Library\Extension\Extension;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

defined('_JEXEC') || die;

trait GetExtensionByIdTrait
{
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
}