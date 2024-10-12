<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;

defined('_JEXEC') || die;

/**
 * Interface to an extension issue test.
 *
 * @since  1.0.0
 */
interface IssueInterface
{
	/**
	 * Invokable class implementation, performing the given test.
	 *
	 * @param   ExtensionInterface  $extension  The extension to test against.
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function __invoke(ExtensionInterface $extension): bool;
}