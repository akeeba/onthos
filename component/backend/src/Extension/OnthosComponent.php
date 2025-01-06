<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Extension;

use Akeeba\Component\ContactUs\Administrator\Service\Html\ContactUs as ContactUsHtml;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Psr\Container\ContainerInterface;

defined('_JEXEC') || die;

class OnthosComponent extends MVCComponent implements BootableExtensionInterface
{
	/** @inheritDoc */
	public function boot(ContainerInterface $container)
	{
		Factory::getApplication()->getLanguage()->load('com_installer', JPATH_ADMINISTRATOR);
	}
}