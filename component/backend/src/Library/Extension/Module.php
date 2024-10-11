<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class Module extends Extension
{
	protected function populateExtensionImportantPaths(): void
	{
		$baseDir = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;

		$this->directories = [
			$this->rebaseToRoot(sprintf("%s/modules/%s", $baseDir, $this->element)),
		];

	}

	protected function populateDefaultLanguageFiles(): void
	{
		$baseDir = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;

		foreach ($this->getKnownLanguages() as $language)
		{
			$this->languageFiles = array_merge(
				$this->languageFiles,
				[
					sprintf("%s/language/%s/%s.ini", $baseDir, $language, $this->element),
					sprintf("%s/language/%s/%s.sys.ini", $baseDir, $language, $this->element),
				]
			);
		}

	}

	protected function addLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		$addons = [];
		$baseDir = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;

		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			$langFolder = $this->getXMLAttribute($siteLangContainer, 'folder', '');
			$langFolder .= empty($langFolder) ? '' : '/';

			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$addons[] = sprintf(
					"%s/language/%s/%s",
					$baseDir,
					$tag,
					basename($relativePath)
				);

				$addons[] = sprintf(
					"%s/modules/%s/%s%s",
					$baseDir,
					$this->element,
					$langFolder,
					$relativePath
				);
			}
		}

		$this->languageFiles = array_merge($this->languageFiles, $this->filterFilesArray($addons, true));
	}

	protected function getScriptPathFromManifest(SimpleXMLElement $xml)
	{
		$baseDir = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;
		$nodes = $xml->xpath('/extension/scriptfile');

		if (empty($nodes))
		{
			return null;
		}

		$fileName = (string) $nodes[0];

		return $this->rebaseToRoot($baseDir . '/modules/' . $this->element . '/' . $fileName);
	}
}