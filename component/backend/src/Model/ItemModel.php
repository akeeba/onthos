<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Model;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class ItemModel extends BaseDatabaseModel
{
	use GetExtensionByIdTrait;

	public function getExistingTables(?ExtensionInterface $extension = null): array
	{
		$tables = $extension?->getTables() ?? [];

		if (empty($tables))
		{
			return [];
		}

		$db        = $this->getDatabase();
		$allTables = $db->getTableList();

		return array_filter(
			$tables,
			fn($table) => in_array($db->replacePrefix($table), $allTables, true)
		);
	}
}