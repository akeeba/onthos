<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class File extends Extension
{
	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateExtensionImportantPaths(): void
	{
		// File extensions do not have a default installation location.
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultLanguageFiles(): void
	{
		$extensionSlug = $this->getExtensionSlug();

		foreach ($this->getKnownLanguages() as $language)
		{
			$this->languageFiles = array_merge(
				$this->languageFiles,
				[
					sprintf("%s/language/%s/%s.ini", JPATH_ROOT, $language, $extensionSlug),
					sprintf("%s/language/%s/%s.sys.ini", JPATH_ROOT, $language, $extensionSlug),
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
		$addons = [];

		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$addons[] = sprintf("%s/language/%s/%s", JPATH_ROOT, $tag, basename($relativePath));
			}
		}

		$this->languageFiles = array_merge($this->languageFiles, $this->filterFilesArray($addons, true));
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function getScriptPathFromManifest(SimpleXMLElement $xml)
	{
		$nodes = $xml->xpath('/extension/scriptfile');

		if (empty($nodes))
		{
			return null;
		}

		$fileName = (string) $nodes[0];

		if (@is_file(JPATH_ROOT . '/' . $fileName))
		{
			return trim($fileName, '/\\');
		}

		if (@is_file($fileName))
		{
			return $this->rebaseToRoot($fileName);
		}

		$bareName = str_starts_with($this->element, 'files_') ? substr($this->element, 6) : $this->element;
		$altPath  = dirname($this->manifestPath) . '/' . $bareName . '/' . $fileName;

		if (@is_file(JPATH_ROOT . '/' . $altPath))
		{
			return $altPath;
		}

		return null;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateExtensionImportantPathsFromManifest(SimpleXMLElement $xml): void
	{
		$nodes = $xml->xpath('/extension/fileset/files');

		foreach ($nodes as $node)
		{
			foreach ($node->children() as $fileNode)
			{
				switch ($fileNode->getName())
				{
					case 'folder':
						$this->directories[] = JPATH_ROOT . '/' . (string) $fileNode;
						break;

					case 'file':
						$this->files[] = JPATH_ROOT . '/' . (string) $fileNode;
						break;

					default:
						continue 2;
				}
			}
		}

		$this->directories = $this->filterDirectoriesArray($this->directories, true);
		$this->files = $this->filterFilesArray($this->files, true);
	}
}