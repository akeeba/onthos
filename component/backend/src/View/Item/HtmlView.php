<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\View\Item;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Mixin\ViewLoadAnyTemplateTrait;
use Akeeba\Component\Onthos\Administrator\Model\ItemModel;
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

	/**
	 * The ID of the extension to display
	 *
	 * @var   int
	 * @since 1.0.0
	 */
	public int $extension_id;

	public ?ExtensionInterface $item;

	public function display($tpl = null)
	{
		/** @var ItemModel $model */
		$model      = $this->getModel();
		$id         = $this->extension_id ?? 0;
		$this->item = $model->getExtensionById($id);

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
		/** @var Toolbar $toolbar */
		$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');

		ToolbarHelper::title(Text::_('COM_ONTHOS'), 'fa fa-poo-storm');

		ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_onthos&view=items', false));
	}

}