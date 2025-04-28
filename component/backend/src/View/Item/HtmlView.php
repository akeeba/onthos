<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\View\Item;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Mixin\ViewLoadAnyTemplateTrait;
use Akeeba\Component\Onthos\Administrator\Model\ItemModel;
use Akeeba\Component\Onthos\Administrator\View\ActionsDropdownTrait;
use Akeeba\Component\Onthos\Administrator\View\DangerZoneDropdownTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View controller for the displaying information about a single extension
 *
 * @since   1.0.0
 */
class HtmlView extends BaseView
{
	use ViewLoadAnyTemplateTrait;
	use ActionsDropdownTrait;
	use DangerZoneDropdownTrait;

	/**
	 * The ID of the extension to display.
	 *
	 * @var   int
	 * @since 1.0.0
	 */
	public int $extension_id;

	/**
	 * The extension item we are working with.
	 *
	 * @var   ExtensionInterface|null
	 * @since 1.0.0
	 */
	public ?ExtensionInterface $item;

	/**
	 * Which of the extension's tables are present in the database (even in a state of disrepair).
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	public array $existingTables;

	/**
	 * @inheritdoc
	 * @sice 1.0.0
	 */
	public function display($tpl = null)
	{
		/** @var ItemModel $model */
		$model                = $this->getModel();
		$id                   = $this->extension_id ?? 0;
		$this->item           = $model->getExtensionById($id);
		$this->existingTables = $model->getExistingTables($this->item);

		if ($this->item === null)
		{
			$this->setLayout($id > 0 ? 'notfound' : 'notselected');
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Populates the toolbar
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	protected function addToolbar(): void
	{
		ToolbarHelper::title(Text::_('COM_ONTHOS'), 'fa fa-poo-storm');
		ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_onthos&view=items', false));

		$actionsBar = $this->addActionsDropdownToobarButton(false);

		// Non-component: Remove Rebuild Menu item
		if ($this->item->type !== 'component')
		{
			$items = $actionsBar->getItems();
			array_pop($items);
			$actionsBar->setItems($items);
		}

		$this->addDangerZoneDropdownToobarButton(false);

		ToolbarHelper::preferences('com_onthos');
	}

}