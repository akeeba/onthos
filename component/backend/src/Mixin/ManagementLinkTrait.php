<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Mixin;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\ManagementLink;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use SimpleXMLElement;

defined('_JEXEC') || die;

trait ManagementLinkTrait
{
	/**
	 * Returns the management links for this extension.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	protected function getManagementLinks(?ExtensionInterface $extension): array
	{
		if ($extension === null)
		{
			return [];
		}

		$ret = [];

		/**
		 * Not installed extension: Link to the Discover page.
		 *
		 * No further links will be provided.
		 */
		if (!$extension->isInstalled())
		{
			$ret['deepDiscover'] = new ManagementLink(
				sprintf(
					'index.php?option=com_installer&view=discover&filter[search]=id:%d',
					$extension->extension_id
				),
				'COM_ONTHOS_ITEM_LBL_LINK_DISCOVER',
				'fa fa-magnifying-glass'
			);

			return $ret;
		}

		// Installed extension: Extensions manager
		$ret['deepExtensionManager'] = new ManagementLink(
			sprintf(
				'index.php?option=com_installer&view=manage&filter[search]=id:%d&filter[status]=&filter[client_id]=&filter[type]=&filter[folder]=&filter[package_id]=&filter[core]=',
				$extension->extension_id
			),
			'COM_ONTHOS_ITEM_LBL_LINK_MANAGE',
			'fa fa-puzzle-piece'
		);

		// Published component with frontend part: All Menu Items, Site
		if ($extension->type === 'component' && $extension->enabled && $extension->getManifest()?->files instanceof SimpleXMLElement)
		{
			$ret['deepMenusSite'] = new ManagementLink(
				sprintf(
					'index.php?option=com_menus&view=items&client_id=0&menutype=&filter[search]&filter[published]=&filter[access]&filter[language]=&filter[level]=&filter[parent_id]=&filter[componentName]=%s',
					$extension->element
				),
				'COM_ONTHOS_ITEM_LBL_LINK_MENUS_SITE',
				'fa fa-list'
			);
		}

		// Published component with backend part: All Menu Items, Admin
		if ($extension->type === 'component' && $extension->enabled && $extension->getManifest()?->administrator instanceof SimpleXMLElement)
		{
			// This actually doesn't work. There is no component filtering in the backend menus 🤡

//			$ret['deepMenusAdmin'] = new ManagementLink(
//				sprintf(
//					'index.php?option=com_menus&view=items&client_id=1&menutype=&filter[search]&filter[published]=&filter[access]&filter[language]=&filter[level]=&filter[parent_id]=&filter[componentName]=%s',
//					$extension->element
//				),
//				'COM_ONTHOS_ITEM_LBL_LINK_MENUS_ADMINISTRATOR',
//				'fa fa-list-alt'
//			);
		}

		// Plugin: plugin manager
		if ($extension->type === 'plugin')
		{
			$ret['deepPlugin'] = new ManagementLink(
				sprintf(
					'index.php?option=com_plugins&filter[search]=%s&filter[status]=&filter[folder]=&filter[element]=&filter[access]=',
					$extension->extension_id
				),
				'COM_ONTHOS_ITEM_LBL_LINK_PLUGINS',
				'fa fa-plug'
			);
		}

		// Site module: module manager
		if ($extension->type === 'module' && $extension->client_id === 0)
		{
			$ret['deepModuleSite'] = new ManagementLink(
				sprintf(
					'option=com_modules&view=modules&client_id=0&filter[search]=&filter[status]=&filter[position]=&filter[module]=%s&filter[menuitem]=&filter[access]=&filter[language]=',
					$extension->element
				),
				'COM_ONTHOS_ITEM_LBL_LINK_MODULE',
				'fa fa-cube'
			);
		}

		// Administrator module: module manager
		if ($extension->type === 'module' && $extension->client_id === 1)
		{
			$ret['deepModuleAdmin'] = new ManagementLink(
				sprintf(
					'option=com_modules&view=modules&client_id=1&filter[search]=&filter[status]=&filter[position]=&filter[module]=%s&filter[menuitem]=&filter[access]=&filter[language]=',
					ltrim($extension->element, 'a')
				),
				'COM_ONTHOS_ITEM_LBL_LINK_MODULE',
				'fa fa-cube'
			);
		}

		// Site template: Template
		if ($extension->type === 'template' && $extension->client_id === 0)
		{
			$ret['deepTemplateSite'] = new ManagementLink(
				sprintf(
					'option=index.php?option=com_templates&view=templates&client_id=0&filter[search]=id:%s',
					$extension->extension_id
				),
				'COM_ONTHOS_ITEM_LBL_LINK_TEMPLATE',
				'fa fa-code'
			);
		}

		// Site template: Template Styles
		if ($extension->type === 'template' && $extension->client_id === 0)
		{
			$ret['deepTemplateStyleSite'] = new ManagementLink(
				sprintf(
					'index.php?option=com_templates&view=styles&client_id=0&filter[search]=&filter[menuitem]=&filter[template]=%s',
					$extension->element
				),
				'COM_ONTHOS_ITEM_LBL_LINK_TEMPLATE_STYLES',
				'fa fa-paintbrush'
			);
		}

		// Administrator template: Template
		if ($extension->type === 'template' && $extension->client_id === 1)
		{
			$ret['deepTemplateAdmin'] = new ManagementLink(
				sprintf(
					'option=index.php?option=com_templates&view=templates&client_id=1&filter[search]=id:%s',
					$extension->extension_id
				),
				'COM_ONTHOS_ITEM_LBL_LINK_TEMPLATE',
				'fa fa-code'
			);
		}

		// Administrator template: Template Styles
		if ($extension->type === 'template' && $extension->client_id === 1)
		{
			$ret['deepTemplateStyleAdmin'] = new ManagementLink(
				sprintf(
					'index.php?option=com_templates&view=styles&client_id=1&filter[search]=&filter[menuitem]=&filter[template]=%s',
					$extension->element
				),
				'COM_ONTHOS_ITEM_LBL_LINK_TEMPLATE_STYLES',
				'fa fa-paintbrush'
			);
		}

		// Language: content language
		if ($extension->type === 'language' && $extension->client_id === 0)
		{
			$ret['deepContentLanguage'] = new ManagementLink(
				sprintf(
					'index.php?option=com_languages&view=languages&filter[published]=&filter[access]=%s',
					$extension->element
				),
				'COM_ONTHOS_ITEM_LBL_LINK_CONTENT_LANGUAGES',
				'fa fa-comment'
			);
		}

		return $ret;
	}

	protected function addManagementDropdownToobarButton(): ?Toolbar
	{
		if (!$this->item)
		{
			return null;
		}

		$managementLinks = $this->getManagementLinks($this->item);

		if (!count($managementLinks))
		{
			return null;
		}

		/** @var Toolbar $toolbar */
		try
		{
			// The Joomla! 5 way
			$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');
		}
		catch (\Throwable)
		{
			// Joomla! 4 fallback
			/** @noinspection PhpDeprecationInspection */
			$toolbar = Toolbar::getInstance('toolbar');
		}

		// Management links drop-down
		$dropdown = $toolbar->dropdownButton('deepLinks')
			->text('COM_ONTHOS_ITEM_LBL_MANAGEMENT_TOOLBAR')
			->toggleSplit(false)
			->icon('fab fa-joomla')
			->buttonClass('btn btn-action')
			->listCheck(false);

		/** @var Toolbar $childBar */
		$childBar = $dropdown->getChildToolbar();

		/**
		 * @var string         $name
		 * @var ManagementLink $link
		 */
		foreach ($managementLinks as $name => $link)
		{
			$childBar->linkButton($name, $link->label, '')
				->url($link->url)
				->text($link->label)
				->icon($link->icon);
		}

		return $childBar;
	}

	private function getComponentName(string $element)
	{
		Factory::getApplication()->getLanguage()->load($element, JPATH_ADMINISTRATOR, null, false, true);

		return Text::_($element);
	}
}