<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Helper;

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') || die;

abstract class OnthosHelper
{
	/**
	 * Create a clickable icon for the Published/Unpublished/Discovered state in the Items view
	 *
	 * @param   int          $value     Current publish state
	 * @param   int          $i         Row counter
	 * @param   bool         $enabled   Is it clickable?
	 * @param   string       $checkbox  Checkbox name
	 * @param   string|null  $formId    The name of the form
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	public static function published(
		int $value, int $i, bool $enabled = true, string $checkbox = 'cb', ?string $formId = 'adminForm'
	): string
	{
		$states = [
			-1 => [
				'task'           => '',
				'text'           => 'COM_ONTHOS_ITEMS_LBL_DISCOVERED',
				'active_title'   => 'COM_ONTHOS_ITEMS_LBL_DISCOVERED',
				'inactive_title' => 'COM_ONTHOS_ITEMS_LBL_DISCOVERED',
				'tip'            => true,
				'active_class'   => ' fa-magnifying-glass',
				'inactive_class' => ' fa-magnifying-glass',
			],
			0  => [
				'task'           => 'publish',
				'text'           => 'JDISABLED',
				'active_title'   => 'COM_INSTALLER_EXTENSION_DISABLED',
				'inactive_title' => 'COM_INSTALLER_EXTENSION_ENABLED',
				'tip'            => true,
				'active_class'   => 'unpublish text-danger border-danger',
				'inactive_class' => 'unpublish text-danger border-danger',
			],
			1  => [
				'task'           => 'unpublish',
				'text'           => 'JENABLED',
				'active_title'   => 'COM_INSTALLER_EXTENSION_ENABLED',
				'inactive_title' => 'COM_INSTALLER_EXTENSION_DISABLED',
				'tip'            => true,
				'active_class'   => 'publish text-success border-success',
				'inactive_class' => 'publish text-success border-success',
			],
		];

		return HTMLHelper::_('jgrid.state', $states, $value, $i, 'items.', $enabled, true, $checkbox, $formId);
	}

	/**
	 * Create a clickable icon for the Protected/Unprotected state in the Items view
	 *
	 * @param   int          $value     Current protected state
	 * @param   int          $i         Row counter
	 * @param   bool         $enabled   Is it clickable?
	 * @param   string       $checkbox  Checkbox name
	 * @param   string|null  $formId    The name of the form
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	public static function protected(
		int $value, int $i, bool $enabled = true, string $checkbox = 'cb', ?string $formId = 'adminForm'
	): string
	{
		$states = [
			0  => [
				'task'           => 'protect',
				'text'           => 'COM_ONTHOS_ITEM_LBL_TOOLBAR_PROTECT',
				'active_title'   => 'COM_ONTHOS_ITEM_LBL_UNPROTECTED',
				'inactive_title' => 'COM_ONTHOS_ITEM_LBL_UNPROTECTED',
				'tip'            => true,
				'active_class'   => ' fa-shield-halved text-success border-success',
				'inactive_class' => ' fa-shield-halved text-success border-success',
			],
			1  => [
				'task'           => 'unprotect',
				'text'           => 'COM_ONTHOS_ITEM_LBL_TOOLBAR_UNPROTECT',
				'active_title'   => 'COM_ONTHOS_ITEM_LBL_PROTECTED',
				'inactive_title' => 'COM_ONTHOS_ITEM_LBL_PROTECTED',
				'tip'            => true,
				'active_class'   => ' fa-shield text-danger border-danger',
				'inactive_class' => ' fa-shield text-danger border-danger',
			],
		];

		return HTMLHelper::_('jgrid.state', $states, $value, $i, 'items.', $enabled, true, $checkbox, $formId);
	}

	/**
	 * Create a clickable icon for the Locked/Unlocked state in the Items view
	 *
	 * @param   int          $value     Current locked state
	 * @param   int          $i         Row counter
	 * @param   bool         $enabled   Is it clickable?
	 * @param   string       $checkbox  Checkbox name
	 * @param   string|null  $formId    The name of the form
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	public static function locked(
		int $value, int $i, bool $enabled = true, string $checkbox = 'cb', ?string $formId = 'adminForm'
	): string
	{
		$states = [
			0  => [
				'task'           => 'lock',
				'text'           => 'COM_ONTHOS_ITEM_LBL_TOOLBAR_LOCK',
				'active_title'   => 'COM_ONTHOS_ITEM_LBL_UNLOCKED',
				'inactive_title' => 'COM_ONTHOS_ITEM_LBL_UNLOCKED',
				'tip'            => true,
				'active_class'   => ' fa-lock-open text-success border-success',
				'inactive_class' => ' fa-lock-open text-success border-success',
			],
			1  => [
				'task'           => 'unlock',
				'text'           => 'COM_ONTHOS_ITEM_LBL_TOOLBAR_UNLOCK',
				'active_title'   => 'COM_ONTHOS_ITEM_LBL_LOCKED',
				'inactive_title' => 'COM_ONTHOS_ITEM_LBL_LOCKED',
				'tip'            => true,
				'active_class'   => ' fa-lock text-danger border-danger',
				'inactive_class' => ' fa-lock text-danger border-danger',
			],
		];

		return HTMLHelper::_('jgrid.state', $states, $value, $i, 'items.', $enabled, true, $checkbox, $formId);
	}


}