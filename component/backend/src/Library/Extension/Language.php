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

	protected function populateDefaultLanguageFiles(): void
	{
		// Language packages don't have their own translation files.
	}

	protected function addLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		// Language packages don't have their own translation files.
	}

	protected function getScriptPathFromManifest(SimpleXMLElement $xml)
	{
		// Language packages do not have installation scripts.
	}

	private function getBasePath(): string
	{
		return [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR, 3 => JPATH_API][$this->client_id] ?? JPATH_SITE;
	}
}