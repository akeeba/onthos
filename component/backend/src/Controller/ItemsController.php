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

class ItemsController extends BaseController
{
	public function __construct(
		$config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null
	)
	{
		parent::__construct(
			array_merge(
				[
					'default_view' => 'items',
					'default_task' => 'default',
				],
				$config
			),
			$factory,
			$app,
			$input
		);
	}

	public function default(): void
	{
		$this->display();
	}
}