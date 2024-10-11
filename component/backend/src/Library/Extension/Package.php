<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

defined('_JEXEC') || die;

use SimpleXMLElement;

class Package extends Extension
{
	protected function populateExtensionImportantPaths(): void
	{
		// Packages do not install files of their own.
	}

	protected function populateDefaultLanguageFiles(): void
	{
		foreach ($this->getKnownLanguages() as $language)
		{
			$this->languageFiles = array_merge(
				$this->languageFiles,
				[
					sprintf("%s/language/%s/%s.ini", JPATH_SITE, $language, $this->element),
					sprintf("%s/language/%s/%s.sys.ini", JPATH_SITE, $language, $this->element),
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
					JPATH_SITE,
					$tag,
					basename($relativePath)
				);
			}
		}

		$this->languageFiles = $this->filterFilesArray($this->languageFiles, true);
	}

	/**
	 * Returns the installation script path read from the XML manifest.
	 *
	 * @param   SimpleXMLElement  $xml
	 *
	 * @return  string|null
	 * @since   1.0.0
	 */
	protected function getScriptPathFromManifest(SimpleXMLElement $xml)
	{
		$nodes = $xml->xpath('/extension/scriptfile');

		if (empty($nodes))
		{
			return null;
		}

		$fileName = (string) $nodes[0];
		$bareName = str_starts_with($this->element, 'pkg_') ? substr($this->element, 4) : $this->element;

		return dirname($this->manifestPath) . '/' . $bareName . '/' . $fileName;
	}

}