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
	public function getManagementLinks(?ExtensionInterface $extension): array
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
			$ret[] = new ManagementLink(
				sprintf(
					'index.php?option=com_installer&view=discover&filter[search]=id:%d',
					$extension->extension_id
				),
				'COM_ONTHOS_ITEM_LBL_LINK_DISCOVER',
				'fa fa-mganifying-glass'
			);

			return $ret;
		}

		// Installed extension: Extensions manager
		$ret[] = new ManagementLink(
			sprintf(
				'index.php?option=com_installer&view=manage&filter[search]=id:%d&filter[status]=&filter[client_id]=&filter[type]=&filter[folder]=&filter[package_id]=&filter[core]=',
				$extension->extension_id
			),
			'COM_ONTHOS_ITEM_LBL_LINK_MANAGE',
			'fa fa-mganifying-glass'
		);

		// Published component with frontend part: All Menu Items, Site
		if ($extension->type === 'component' && $extension->enabled && $extension->getManifest()?->files instanceof SimpleXMLElement)
		{
			$ret[] = new ManagementLink(
				sprintf(
					'index.php?option=com_menus&view=items&client_id=0&menutype=&filter[search]&filter[published]=&filter[access]&filter[language]=&filter[level]=&filter[parent_id]=&filter[componentName]=%s',
					$this->getComponentName($extension->element)
				),
				'COM_ONTHOS_ITEM_LBL_LINK_MENUS_SITE',
				'fa fa-list'
			);
		}

		// Published component with backend part: All Menu Items, Admin
		if ($extension->type === 'component' && $extension->enabled && $extension->getManifest()?->administrator instanceof SimpleXMLElement)
		{
			$ret[] = new ManagementLink(
				sprintf(
					'index.php?option=com_menus&view=items&client_id=1&menutype=&filter[search]&filter[published]=&filter[access]&filter[language]=&filter[level]=&filter[parent_id]=&filter[componentName]=%s',
					$this->getComponentName($extension->element)
				),
				'COM_ONTHOS_ITEM_LBL_LINK_MENUS_ADMINISTRATOR',
				'fa fa-list'
			);
		}

		// Plugin: plugin manager
		if ($extension->type === 'plugin')
		{
			$ret[] = new ManagementLink(
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
			$ret[] = new ManagementLink(
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
			$ret[] = new ManagementLink(
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
			$ret[] = new ManagementLink(
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
			$ret[] = new ManagementLink(
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
			$ret[] = new ManagementLink(
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
			$ret[] = new ManagementLink(
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
			$ret[] = new ManagementLink(
				sprintf(
					'index.php?option=com_languages&view=languages&filter[published]=&filter[access]=',
					$extension->element
				),
				'COM_ONTHOS_ITEM_LBL_LINK_CONTENT_LANGUAGES',
				'fa fa-comment'
			);
		}

		return $ret;
	}

	private function getComponentName(string $element)
	{
		Factory::getApplication()->getLanguage()->load($element, JPATH_ADMINISTRATOR, null, false, true);

		return Text::_($element);
	}
}