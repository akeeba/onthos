<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class Component extends Extension
{
	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateExtensionImportantPaths(): void
	{
		$this->directories = [
			sprintf("components/%s", $this->element),
			$this->rebaseToRoot(sprintf("%s/components/%s", JPATH_ADMINISTRATOR, $this->element)),
			$this->rebaseToRoot(sprintf("%s/components/%s", JPATH_API, $this->element)),
		];

	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultLanguageFiles(): void
	{
		foreach ($this->getKnownLanguages() as $language)
		{
			$this->languageFiles = array_merge(
				$this->languageFiles,
				[
					sprintf("%s/language/%s/%s.ini", JPATH_ROOT, $language, $this->element),
					sprintf("%s/language/%s/%s.sys.ini", JPATH_ROOT, $language, $this->element),
					sprintf("%s/language/%s/%s.ini", JPATH_ADMINISTRATOR, $language, $this->element),
					sprintf("%s/language/%s/%s.sys.ini", JPATH_ADMINISTRATOR, $language, $this->element),
					sprintf("%s/language/%s/%s.ini", JPATH_API, $language, $this->element),
					sprintf("%s/language/%s/%s.sys.ini", JPATH_API, $language, $this->element),
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
		// Admin language files
		foreach ($xml->xpath('/extension/administration/languages') as $adminLangContainer)
		{
			$langFolder = $this->getXMLAttribute($adminLangContainer, 'folder', '');
			$langFolder .= empty($langFolder) ? '' : '/';

			foreach ($adminLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->languageFiles[] = sprintf(
					"%s/language/%s/%s", JPATH_ADMINISTRATOR, $tag, basename($relativePath)
				);
				$this->languageFiles[] = sprintf(
					"%s/components/%s/%s%s",
					JPATH_ADMINISTRATOR,
					$this->element,
					$langFolder,
					$relativePath
				);
			}
		}

		// API language files
		foreach ($xml->xpath('/extension/api/languages') as $apiLangContainer)
		{
			$langFolder = $this->getXMLAttribute($apiLangContainer, 'folder', '');
			$langFolder .= empty($langFolder) ? '' : '/';

			foreach ($apiLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->languageFiles[] = sprintf("%s/language/%s/%s", JPATH_API, $tag, basename($relativePath));
				$this->languageFiles[] = sprintf(
					"%s/components/%s/%s%s",
					JPATH_API,
					$this->element,
					$langFolder,
					$relativePath
				);
			}
		}

		// Frontend language files
		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			$langFolder = $this->getXMLAttribute($siteLangContainer, 'folder', '');
			$langFolder .= empty($langFolder) ? '' : '/';

			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->languageFiles[] = sprintf("%s/language/%s/%s", JPATH_ROOT, $tag, basename($relativePath));
				$this->languageFiles[] = sprintf(
					"%s/components/%s/%s%s",
					JPATH_ROOT,
					$this->element,
					$langFolder,
					$relativePath
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

		return $this->rebaseToRoot(JPATH_ADMINISTRATOR . '/components/' . $this->element . '/' . $fileName);
	}

}