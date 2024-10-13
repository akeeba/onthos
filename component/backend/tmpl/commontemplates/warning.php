<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Component\ComponentHelper;

if (ComponentHelper::getComponent('com_onthos')->params->get('show_warning', 1) == 0)
{
	return;
}
?>

<div class="alert alert-info" role="alert">
	<h3 class="alert-heading">
		<span class="fa fa-fw fa-bolt-lightning" aria-hidden="true"></span>
		With great power comes great responsibility
	</h3>
	<p>
		Unlike Joomla! itself, Onthos does not provide any protections against accidentally breaking your site. You are
		strongly advised to take a backup and keep it off-site, e.g. on your local computer, before applying any action
		in Onthos.
	</p>
	<p class="text-muted small">
		This warning can be disabled in the Options page.
	</p>
</div>