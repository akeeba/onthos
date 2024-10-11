<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\Component\Onthos\Administrator\View\Main\HtmlView $item */

$item = $this->getModel()->getExtensionByDetails('component', 'com_akeebabackup');

echo $this->loadAnyTemplate('commontemplates/summary', false, ['item' => $item]);