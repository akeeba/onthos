<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class Template extends Extension
{
	protected function populateExtensionImportantPaths(): void
	{
		$basePath = $this->getBasePath();

		if ($basePath === JPATH_BASE)
		{
			$themePath = JPATH_THEMES;
		}
		else
		{
			$themePath = $basePath . '/templates';
		}

		$this->directories = [
			$this->rebaseToRoot(
				sprintf(
					"%s/%s",
					$themePath,
					$this->element
				)
			),
		];
	}

	protected function populateDefaultLanguageFiles(): void
	{
		$basePath = $this->getBasePath();

		foreach ($this->getKnownLanguages() as $language)
		{
			$this->languageFiles = array_merge(
				$this->languageFiles,
				[
					sprintf("%s/language/%s/tpl_%s.ini", $basePath, $language, $this->element),
					sprintf("%s/language/%s/tpl_%s.sys.ini", $basePath, $language, $this->element)
				]
			);
		}

	}

	protected function addLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		$basePath = $this->getBasePath();
		$addons = [];

		if ($basePath === JPATH_BASE)
		{
			$themePath = JPATH_THEMES;
		}
		else
		{
			$themePath = $basePath . '/templates';
		}

		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			$langFolder = $this->getXMLAttribute($siteLangContainer, 'folder', '');
			$langFolder .= empty($langFolder) ? '' : '/';

			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$addons[] = sprintf("%s/language/%s/%s", $basePath, $tag, basename($relativePath));
				$addons[] = sprintf(
					"%s/%s/%s%s",
					$themePath,
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
		$basePath = $this->getBasePath();

		if ($basePath === JPATH_BASE)
		{
			$themePath = JPATH_THEMES;
		}
		else
		{
			$themePath = $basePath . '/templates';
		}

		$nodes = $xml->xpath('/extension/scriptfile');

		if (empty($nodes))
		{
			return null;
		}

		$fileName = (string) $nodes[0];

		return $this->rebaseToRoot(sprintf("%s/%s/%s", $themePath, $this->element, $fileName));
	}

	private function getBasePath(): string
	{
		return [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;
	}

}