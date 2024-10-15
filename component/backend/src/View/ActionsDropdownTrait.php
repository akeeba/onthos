<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\View;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;

trait ActionsDropdownTrait
{
	protected function addActionsDropdownToobarButton(bool $listCheck = true): void
	{
		/** @var Toolbar $toolbar */
		$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');

		// Actions drop-down
		$dropdown = $toolbar->dropdownButton('status-group')
			->text('JTOOLBAR_CHANGE_STATUS')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action')
			->listCheck($listCheck);

		/** @var Toolbar $childBar */
		$childBar = $dropdown->getChildToolbar();

		$childBar->publish('items.publish')
			->icon('fa fa-check-circle')
			->text('JTOOLBAR_ENABLE')
			->listCheck($listCheck);

		$childBar->publish('items.unpublish')
			->icon('fa fa-times-circle')
			->text('JTOOLBAR_DISABLE')
			->listCheck($listCheck);

		$childBar->publish('items.protect')
			->icon('fa fa-shield')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_PROTECT')
			->listCheck($listCheck);

		$childBar->publish('items.unprotect')
			->icon('fa fa-shield-halved')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_UNPROTECT')
			->listCheck($listCheck);

		$childBar->publish('items.lock')
			->icon('fa fa-lock')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_LOCK')
			->listCheck($listCheck);

		$childBar->publish('items.unlock')
			->icon('fa fa-lock-open')
			->text('COM_ONTHOS_ITEM_LBL_TOOLBAR_UNLOCK')
			->listCheck($listCheck);

	}
}