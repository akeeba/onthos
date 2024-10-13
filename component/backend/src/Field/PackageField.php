<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Field;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;

/**
 * Field class to list Package extensions
 *
 * @since   1.0.0
 */
class PackageField extends ListField
{
	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	protected $type = 'Package';

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), $this->getPackages());
	}

	/**
	 * Get the distinct packages from the site's database.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	private function getPackages(): array
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query
			->select(
				$db->quoteName(
					[
						'extension_id',
						'name',
						'element',
					]
				)
			)
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('package'));
		$extensions = $db->setQuery($query)->loadObjectList() ?: [];

		if (empty($extensions))
		{
			return [];
		}

		$language    = Factory::getApplication()->getLanguage();
		$arrayKeys   = array_map(
			function (object $entry) use ($language): string {
				$language->load("{$entry->element}.sys", JPATH_SITE)
				|| $language->load($entry->element, JPATH_SITE);
				$language->load("{$entry->element}.sys", JPATH_ADMINISTRATOR)
				|| $language->load($entry->element, JPATH_ADMINISTRATOR);

				return Text::_($entry->name);
			},
			$extensions
		);
		$arrayValues = array_map(
			function (object $entry): int {
				return $entry->extension_id;
			},
			$extensions
		);

		$extensions = array_combine($arrayKeys, $arrayValues);
		ksort($extensions);

		$options = [];

		foreach ($extensions as $label => $id)
		{
			$options[] = HTMLHelper::_('select.option', $id, $label);
		}

		return $options;
	}


}