<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Issues;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Test for having no canonical update site enabled.
 *
 * The manifest has an update site, it exists in the database, but it's currently disabled.
 *
 * @since   1.0.0
 */
class DisabledUpdateSite extends Issues\AbstractIssue
{
	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::ERROR;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		// TODO Enable update site

		return parent::getDetailsTemplate();
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function doTest(): bool
	{
		$canonicalUpdateServers = $this->extension->getCanonicalUpdateServers();

		if (empty($canonicalUpdateServers))
		{
			return false;
		}

		// First, make sure at least one canonical update server exists in the database
		$hasCanonical = array_reduce(
			$this->extension->getUpdateSites(),
			fn(bool $carry, object $currentUpdateServer) => $carry
				? $carry
				: in_array($currentUpdateServer->location, $canonicalUpdateServers, true),
			false
		);

		if (!$hasCanonical)
		{
			return false;
		}

		// Then, check if any canonical update server is enabled
		return !array_reduce(
			$this->extension->getUpdateSites(),
			fn(bool $carry, object $currentUpdateServer) => $carry || !$currentUpdateServer->enabled
				? $carry
				: in_array($currentUpdateServer->location, $canonicalUpdateServers, true),
			false
		);
	}
}