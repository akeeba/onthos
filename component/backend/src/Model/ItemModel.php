<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Model;

defined('_JEXEC') || die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class ItemModel extends BaseDatabaseModel
{
	use GetExtensionByIdTrait;
}