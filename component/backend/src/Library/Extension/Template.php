<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

use SimpleXMLElement;

defined('_JEXEC') || die;

class Template extends Extension
{
	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultExtensionPaths(): void
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

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultLanguages(): void
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

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		$basePath = $this->getBasePath();
		$this->languageFiles = [];

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

				$this->addAlternativeLanguageFiles(
					[0 => 'site', 1 => 'administrator'][$this->client_id] ?? 'site',
					sprintf("%s/language/%s/%s", $basePath, $tag, basename($relativePath)),
					sprintf(
						"%s/%s/%s%s",
						$themePath,
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

	/**
	 * Returns the base path depending on the client ID.
	 *
	 * @return  string  The base path which is either JPATH_SITE or JPATH_ADMINISTRATOR.
	 * @since   1.0.0
	 */
	private function getBasePath(): string
	{
		return [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR][$this->client_id] ?? JPATH_SITE;
	}

}