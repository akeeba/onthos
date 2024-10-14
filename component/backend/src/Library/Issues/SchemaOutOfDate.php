<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Tests for an outdated database schema.
 *
 * Only if there is a corresponding #__schemas entry. Joomla! reports one or more database schema issues for the
 * extension.
 *
 * @since   1.0.0
 */
class SchemaOutOfDate extends AbstractIssue
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::WARNING;
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		// TODO

		return parent::getDetailsTemplate();
	}

	/**
	 * @inheritDoc
	 * @since  1.0.0
	 */
	protected function doTest(): bool
	{
		// If the extension does not have a `#__schemas` entry the MissingTables issue is more appropriate.
		if (!$this->extension->hasSchemasEntry())
		{
			return false;
		}

		$schemaErrors = array_filter(
			$this->extension->getSchemasErrors(),
			fn(array $error) => $error['extension']?->extension_id == $this->extension->extension_id
			                    && $error['errorsCount'] > 0
		);

		return count($schemaErrors) > 0;
	}
}