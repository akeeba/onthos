<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\View;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;

trait DangerZoneDropdownTrait
{
	protected function addDangerZoneDropdownToobarButton(bool $listCheck = true): void
	{
		/** @var Toolbar $toolbar */
		try
		{
			// The Joomla! 5 way
			$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');
		}
		catch (\Throwable)
		{
			// Joomla! 4 fallback
			$toolbar = Toolbar::getInstance('toolbar');
		}

		// Danger Zone drop-down
		$dropdown = $toolbar->dropdownButton('dangerzone-group')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_DANGERZONE')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action btn-danger')
			->listCheck($listCheck);

		/** @var Toolbar $childBar */
		$childBar = $dropdown->getChildToolbar();

		$childBar->publish('items.juninstall')
			->icon('fa fa-trash-can')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_STANDARDUNINSTALL')
			->listCheck($listCheck);

		$childBar->publish('items.uninstall')
			->icon('fa fa-hammer')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_UNINSTALL')
			->listCheck($listCheck);

		$childBar->publish('items.noscript')
			->icon('fa fa-fire')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_NOSCRIPT')
			->listCheck($listCheck);

		$childBar->publish('items.forced')
			->icon('fa fa-skull-crossbones')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_FORCED')
			->listCheck($listCheck);
	}
}