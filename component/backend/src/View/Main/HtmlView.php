<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\View\Main;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	public function display($tpl = null)
	{
		// TODO

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar(): void
	{
		/** @var Toolbar $toolbar */
		$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');

		ToolbarHelper::title(Text::_('COM_ONTHOS'), 'fa fa-list-alt');
	}
}