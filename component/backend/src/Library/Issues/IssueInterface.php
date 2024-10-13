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
	public function __construct(ExtensionInterface $extension);

	/**
	 * Invokable class implementation, performing the given test.
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function __invoke(): bool;

	/**
	 * Returns an icon class for displaying in the interface.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	public function getIcon(): string;

	/**
	 * Returns the human-readable label for displaying in the interface.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	public function getLabel(): string;

	/**
	 * Returns the human-readable description for displaying in the interface.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	public function getDescription();

	/**
	 * Returns the severity rating of this issue.
	 *
	 * The default interface convention for displaying issues are:
	 * - emergency, alert: danger text, bold font weight.
	 * - critical: danger text, semibold font weight.
	 * - error: danger text, normal font weight.
	 * - warning: warning text, semibold font weight.
	 * - notice: warning text, normal font weight.
	 * - info: info text, normal font weight.
	 * - debug: muted text, light font weight.
	 *
	 * The semantic meanings of severity ratings are:
	 * - emergency, alert: must be fixed, will cause major issues
	 * - critical: (unused)
	 * - error: (unused)
	 * - warning: fixing is recommended, will cause minor issues
	 * - notice: (unused)
	 * - info: fixing is optional, possibly not important
	 * - debug: we are uncertain if something is broken. Pretty much reserved for "core extension is broken"
	 *
	 * @return  string
	 * @since   1.0.0
	 * @see     \Psr\Log\LogLevel
	 */
	public function getSeverity(): string;

	/**
	 * Returns a view template which provides further details, and recommendations for fixing this issue.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	public function getDetailsTemplate(): string;

	/**
	 * Handles the execution of a proposed fix.
	 *
	 * If the fix fails it should raise an Exception. It will be reported back to the user.
	 *
	 * @param   string  $fixAction  An optional fix action, in case there are more than one available.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function fix(string $fixAction = 'default'): void;
}