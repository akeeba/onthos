<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * Empty state. If you see this, something has really gone incredibly wrong with your site. My condolences.
 *
 * @var \Akeeba\Component\Onthos\Administrator\View\Main\HtmlView $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_ONTHOS_MAIN',
	'formURL'    => 'index.php?option=com_onthos&view=main',
	'icon'       => 'fa fa-poo-storm',
];

$user = Factory::getApplication()->getIdentity();

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
