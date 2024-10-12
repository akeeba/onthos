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
	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultExtensionPaths(): void
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

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultLanguages(): void
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
	protected function populateExtensionPathsFromManifest(SimpleXMLElement $xml): void
	{
		$this->directories = [];
		$this->files       = [];

		if ($items = $xml->xpath('/extension/files'))
		{
			$base                = sprintf("%s/%s/%s", JPATH_PLUGINS, $this->folder, $this->element);
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

		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			foreach ($siteLangContainer->children() as $node)
			{
				$langFolder = $this->getXMLAttribute($siteLangContainer, 'folder', '');
				$langFolder .= empty($langFolder) ? '' : '/';

				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->addAlternativeLanguageFiles(
					sprintf(
						"%s/language/%s/%s",
						JPATH_ADMINISTRATOR,
						$tag,
						basename($relativePath)
					),
					sprintf(
						"%s/plugins/%s/%s/%s%s",
						JPATH_ROOT,
						$this->folder,
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
		$nodes = $xml->xpath('/extension/scriptfile');

		if (empty($nodes))
		{
			return null;
		}

		$fileName = (string) $nodes[0];

		return $this->rebaseToRoot(JPATH_PLUGINS . '/' . $this->folder . '/' . $this->element . '/' . $fileName);
	}

}