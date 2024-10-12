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
	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultExtensionPaths(): void
	{
		$baseDir = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;

		$this->directories = [
			$this->rebaseToRoot(sprintf("%s/modules/%s", $baseDir, $this->element)),
		];

		$this->files = [
			$this->rebaseToRoot(sprintf("%s/modules/%s.php", $baseDir, $this->element)),
		];
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultLanguages(): void
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

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateExtensionPathsFromManifest(SimpleXMLElement $xml): void
	{
		$this->directories = [];
		$this->files       = [];

		$baseDir = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;

		if ($items = $xml->xpath('/extension/files'))
		{
			$base                = sprintf("%s/modules/%s", $baseDir, $this->element);
			$this->directories[] = $base;

			foreach ($items[0]->children() as $item)
			{
				if ($item->getName() === 'file' || $item->getName() === 'filename')
				{
					$this->files[] = $base . '/' . (string) $item;
				}
				elseif ($this->getName() === 'folder')
				{
					$this->directories[] = $base . '/' . (string) $item;
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		$this->languageFiles = [];
		$baseDir = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;

		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			$langFolder = $this->getXMLAttribute($siteLangContainer, 'folder', '');
			$langFolder .= empty($langFolder) ? '' : '/';

			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->addAlternativeLanguageFiles(
					sprintf(
						"%s/language/%s/%s",
						$baseDir,
						$tag,
						basename($relativePath)
					),
					sprintf(
						"%s/modules/%s/%s%s",
						$baseDir,
						$this->element,
						$langFolder,
						$relativePath
					)
				);
			}
		}
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
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