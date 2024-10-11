<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class Language extends Extension
{
	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateExtensionImportantPaths(): void
	{
		$basePath = $this->getBasePath();

		$this->directories = [
			$this->rebaseToRoot(
				sprintf(
					"%s/language/%s",
					$basePath,
					$this->element
				)
			),
		];
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultLanguageFiles(): void
	{
		// Language packages don't have their own translation files.
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function addLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		// Language packages don't have their own translation files.
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function getScriptPathFromManifest(SimpleXMLElement $xml)
	{
		// Language packages do not have installation scripts.
	}

	/**
	 * Returns the base path depending on the client ID.
	 *
	 * @return  string  The base path which is either JPATH_SITE or JPATH_ADMINISTRATOR.
	 * @since   1.0.0
	 */
	private function getBasePath(): string
	{
		return [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR, 3 => JPATH_API][$this->client_id] ?? JPATH_SITE;
	}
}