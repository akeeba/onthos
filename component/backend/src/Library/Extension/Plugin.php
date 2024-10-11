<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class Plugin extends Extension
{
	protected function populateExtensionImportantPaths(): void
	{
		$this->directories = [
			// This is the expected path since Joomla! 1.5
			$this->rebaseToRoot(sprintf("%s/%s/%s", JPATH_PLUGINS, $this->folder, $this->element)),
		];

		$this->files = [
			// This is the legacy support dating back to Joomla! 1.0 AND IT STILL WORKS, DANG IT!
			$this->rebaseToRoot(sprintf("%s/%s/%s", JPATH_PLUGINS, $this->folder, $this->element . '.php')),
		];
	}

	protected function populateDefaultLanguageFiles(): void
	{
		foreach ($this->getKnownLanguages() as $language)
		{
			$this->languageFiles = array_merge(
				$this->languageFiles,
				[
					sprintf("%s/language/%s/plg_%s_%s.ini", JPATH_ADMINISTRATOR, $language, $this->folder, $this->element),
					sprintf("%s/language/%s/plg_%s_%s.sys.ini", JPATH_ADMINISTRATOR, $language, $this->folder, $this->element),
				]
			);
		}
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function addLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		// Frontend language files
		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->languageFiles[] = sprintf(
					"%s/language/%s/%s",
					JPATH_ADMINISTRATOR,
					$tag,
					basename($relativePath)
				);

				$this->languageFiles[] = sprintf(
					"%s/plugins/%s/%s/%s",
					JPATH_ROOT,
					$this->folder,
					$this->element,
					$relativePath
				);
			}
		}

		$this->languageFiles = $this->filterFilesArray($this->languageFiles, true);
	}
}