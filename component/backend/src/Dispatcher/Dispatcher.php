<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Dispatcher;

defined('_JEXEC') || die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Throwable;

class Dispatcher extends ComponentDispatcher
{
	protected $defaultController = 'items';

	public function dispatch()
	{
		try
		{
			if (!$this->app->getIdentity()?->authorise('core.admin'))
			{
				throw new \RuntimeException($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 500);
			}

			// Check the minimum supported PHP version
			$minPHPVersion = '8.1.0';
			$softwareName  = 'Onthos';

			if (!version_compare(PHP_VERSION, $minPHPVersion))
			{
				throw new \RuntimeException(
					sprintf(
						'%s requires PHP %s or later',
						$softwareName,
						$minPHPVersion
					)
				);
			}

			$jLang = $this->app->getLanguage();
			$jLang->load($this->option, JPATH_ADMINISTRATOR, null, true, true);
			$jLang->load($this->option, JPATH_SITE, null, true, true);

			$this->applyViewAndController();

			$this->loadCommonStaticMedia();

			parent::dispatch();
		}
		catch (Throwable $e)
		{
			$title = 'Onthos';
			$isPro = false;

			if (!(include_once __DIR__ . '/../../tmpl/commontemplates/errorhandler.php'))
			{
				throw $e;
			}
		}
	}

	private function applyViewAndController(): void
	{
		// Handle a custom default controller name
		$view       = $this->input->getCmd('view', $this->defaultController);
		$controller = $this->input->getCmd('controller', $view);
		$task       = $this->input->getCmd('task', 'items');

		// Check for a controller.task command.
		if (str_contains($task, '.'))
		{
			// Explode the controller.task command.
			[$controller, $task] = explode('.', $task);
		}

		$this->input->set('view', $controller);
		$this->input->set('controller', $controller);
		$this->input->set('task', $task);
	}

	/**
	 * Loads static media files common across all backend views
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function loadCommonStaticMedia(): void
	{
		// Make sure we run under a CMS application
		if (!($this->app instanceof CMSApplication))
		{
			return;
		}

		// Make sure the document is HTML
		$document = $this->app->getDocument();

		if (!($document instanceof HtmlDocument))
		{
			return;
		}

		$webAssetManager = $document->getWebAssetManager();
		$webAssetManager->useStyle('com_onthos.backend');
	}

}