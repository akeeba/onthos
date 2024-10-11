<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;
use SimpleXMLElement;

defined('_JEXEC') || die;

class Component extends Extension
{
	protected function init(): void
	{
		if (empty($this->element ?? null))
		{
			return;
		}

		// Extension important directories: backend, frontend, and API paths
		$this->directories = [
			sprintf("components/%s", $this->element),
			$this->rebaseToRoot(sprintf("%s/components/%s", JPATH_ADMINISTRATOR, $this->element)),
			$this->rebaseToRoot(sprintf("%s/components/%s", JPATH_API, $this->element)),
		];

		// Default language files
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

		$this->languageFiles = $this->filterFilesArray($this->languageFiles, true);

		// Default media path
		$this->populateMediaPathsFromDefault();

		// Use the default SQL files to populate the tables
		$this->populateTablesFromDefaultDirectory();

		// Discover the manifest
		$this->manifestPath = $this->getManifestXMLPath();

		try
		{
			$xml = InstallerHelper::getInstallationXML($this->element, $this->type, $this->client_id, $this->folder);
		}
		catch (\Throwable $e)
		{
			$xml = null;
		}

		if (!$xml instanceof SimpleXMLElement)
		{
			return;
		}

		$this->manifestPath = $this->rebaseToRoot($this->manifestPath);

		if ($this->getXMLAttribute($xml, 'type') !== 'component')
		{
			return;
		}

		// Language files from the manifest
		$this->addLanguagesFromManifest($xml);

		// Media directory from the manifest
		$this->addMediaDirectoriesFromManifest($xml);

		// Script file from the manifest
		$this->scriptPath = $this->getScriptPathFromManifest($xml);

		// Populate the tables from the manifest
		$this->populateTablesFromManifest($xml);
	}

	/**
	 * Get the paths to language files reading the manifest information
	 *
	 * @param   SimpleXMLElement  $xml
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function addLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		// Admin language files
		foreach ($xml->xpath('/extension/administration/languages') as $adminLangContainer)
		{
			foreach ($adminLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->languageFiles[] = sprintf(
					"%s/language/%s/%s", JPATH_ADMINISTRATOR, $tag, basename($relativePath)
				);
				$this->languageFiles[] = JPATH_ADMINISTRATOR . '/components/' . $this->element . '/' . $relativePath;
			}
		}

		// API language files
		foreach ($xml->xpath('/extension/api/languages') as $apiLangContainer)
		{
			foreach ($apiLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->languageFiles[] = sprintf("%s/language/%s/%s", JPATH_API, $tag, basename($relativePath));
				$this->languageFiles[] = JPATH_API . '/components/' . $this->element . '/' . $relativePath;
			}
		}

		// Frontend language files
		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->languageFiles[] = sprintf("%s/language/%s/%s", JPATH_ROOT, $tag, basename($relativePath));
				$this->languageFiles[] = JPATH_ROOT . '/components/' . $this->element . '/' . $relativePath;
			}
		}

		$this->languageFiles = $this->filterFilesArray($this->languageFiles, true);
	}
}