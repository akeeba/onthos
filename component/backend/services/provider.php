<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Extension\OnthosComponent;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The banners service provider.
 *
 * @since  9.0.0
 */
return new class implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   9.0.0
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider(new MVCFactory('Akeeba\\Component\\Onthos'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('Akeeba\\Component\\Onthos'));

		$container->set(
			ComponentInterface::class,
			function (Container $container) {
				try
				{
					require_once JPATH_ADMINISTRATOR . '/components/com_onthos/vendor/autoload.php';
				}
				catch (Throwable $e)
				{
					// Well, we tried…
				}

				$component = new OnthosComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $component;
			}
		);
	}
};
