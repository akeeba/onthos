<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class Library extends Extension
{
	protected function populateExtensionImportantPaths(): void
	{
		$this->directories[] = [
			$this->rebaseToRoot(
				sprintf("%s/%s", JPATH_LIBRARIES, $this->element)
			)
		];
	}

	protected function populateExtensionImportantPathsFromManifest(SimpleXMLElement $xml): void
	{
		$this->directories = [];
		$this->files       = [];

		foreach ($xml->xpath('/extension/files') as $filesNode)
		{
			$basePath = JPATH_LIBRARIES . '/' . $this->getXMLAttribute($filesNode, 'folder', $this->element);

			$this->directories[] = $this->rebaseToRoot($basePath);

			foreach ($filesNode->children() as $fileNode)
			{
				switch ($fileNode->getName())
				{
					case 'file':
						$this->files[] = $this->rebaseToRoot($basePath . '/' . (string) $fileNode);
						break;

					case 'folder':
						$this->directories[] = $this->rebaseToRoot($basePath . '/' . (string) $fileNode);
						break;
				}
			}
		}
	}

	protected function populateDefaultLanguageFiles(): void
	{
		foreach ($this->getKnownLanguages() as $language)
		{
			$this->languageFiles = array_merge(
				$this->languageFiles,
				[
					sprintf("%s/language/%s/lib_%s.ini", JPATH_ROOT, $language, $this->element),
					sprintf("%s/language/%s/lib_%s.sys.ini", JPATH_ROOT, $language, $this->element),
				]
			);
		}
	}

	protected function addLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		$addons = [];

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
					JPATH_ROOT,
					$tag,
					basename($relativePath)
				);

				$addons[] = sprintf(
					"%s/modules/%s/%s%s",
					JPATH_ROOT,
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
		$nodes = $xml->xpath('/extension/scriptfile');

		if (empty($nodes))
		{
			return null;
		}

		$fileName = (string) $nodes[0];
		$bareName = str_starts_with($this->element, 'lib_') ? substr($this->element, 4) : $this->element;

		return $this->rebaseToRoot(JPATH_LIBRARIES . '/' . $bareName . '/' . $fileName);
	}
}