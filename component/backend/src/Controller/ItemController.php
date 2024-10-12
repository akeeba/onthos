<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Controller;

defined('_JEXEC') || die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Controller to display or apply actions to a single extension.
 *
 * @since   1.0.0
 */
class ItemController extends BaseController
{
	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function __construct(
		$config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null
	)
	{
		parent::__construct(
			array_merge(
				[
					'default_view' => 'item',
					'default_task' => 'read',
				],
				$config
			),
			$factory,
			$app,
			$input
		);
	}

	/**
	 * Display the information summary of a single extension
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	public function read(): void
	{
		// Get the view
		$document   = $this->app->getDocument();
		$viewType   = $document->getType();
		$viewName   = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');
		$view       = $this->getView(
			$viewName, $viewType, '', ['base_path' => $this->basePath, 'layout' => $viewLayout]
		);

		// Set the extension ID
		$view->extension_id = max(0, intval($this->input->getInt('id', 0) ?: 0));

		// Display the view
		$this->display();
	}
}