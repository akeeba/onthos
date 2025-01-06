<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Field;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;

/**
 * Field class to list plugin groups
 *
 * @since   1.0.0
 */
class FolderField extends ListField
{
	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	protected $type = 'Folder';

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), $this->getFolders());
	}

	/**
	 * Get the distinct plugin folders from the site's database.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	private function getFolders(): array
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select('DISTINCT ' . $db->quoteName('folder'))
			->from($db->quoteName('#__extensions'))
			->order($db->quoteName('folder'));

		try
		{
			return array_map(
				fn($folder) => HTMLHelper::_('select.option', $folder, $folder),
				array_filter($db->setQuery($query)->loadColumn() ?: [])
			);
		}
		catch (\Exception $e)
		{
			return [];
		}
	}


}