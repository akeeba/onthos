<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Library\Issues;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Test for having the wrong update site.
 *
 * The manifest has an update site, but the extension has a different update site in the database marked as enabled.
 *
 * @since   1.0.0
 */
class WrongUpdateSite extends Issues\AbstractIssue
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
		return 'issues/rebuild_update_site';
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function doTest(): bool
	{
		/**
		 * This test does not apply to core extensions such as file_joomla. Users are allowed to use alternative update
		 * sites for the core. Other core extensions do not _actually_ have update sites. They just pretend to.
		 */
		if ($this->extension->isCore())
		{
			return false;
		}

		$canonicalUpdateServers = $this->extension->getCanonicalUpdateServers();

		if (empty($canonicalUpdateServers))
		{
			return false;
		}

		$enabledLocations = array_map(
			fn($x) => $x->location,
			array_filter(
				$this->extension->getUpdateSites(),
				fn($x) => $x->enabled
			)
		);

		if (empty($enabledLocations))
		{
			return false;
		}

		foreach ($enabledLocations as $location)
		{
			if (!in_array($location, $canonicalUpdateServers, true))
			{
				return true;
			}
		}

		return false;
	}
}