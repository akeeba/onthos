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
		// TODO Regenerate update sites

		return parent::getDetailsTemplate();
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function doTest(): bool
	{
		if (!$this->extension->getCanonicalUpdateServers())
		{
			return false;
		}

		$canonicalUpdateServers = $this->extension->getCanonicalUpdateServers();

		return array_reduce(
			$this->extension->getUpdateSites(),
			fn(bool $carry, object $currentUpdateServer) => ($carry || !$currentUpdateServer->enabled)
				? $carry
				: in_array($currentUpdateServer->location, $canonicalUpdateServers, true),
			false
		);
	}
}