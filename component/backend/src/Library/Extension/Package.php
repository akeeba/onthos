<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use SimpleXMLElement;

class Package extends Extension
{
	/**
	 * Used to return subextension objects
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	private array $subExtensionCriteria = [];

	/**
	 * Gets the subextensions of a package.
	 *
	 * This can be used to re-adopt orphaned extensions without having to re-install the package they came with.
	 *
	 * @return  array<ExtensionInterface>
	 * @since   1.0.0
	 */
	public function getSubextensionObjects(): array
	{
		static $subExtensions = null;

		return $subExtensions ??= array_filter(
			array_map([$this, 'criteriaToExtension'], $this->subExtensionCriteria)
		);
	}

	protected function onAfterManifestFound(SimpleXMLElement $xml)
	{
		$this->subExtensionCriteria = [];

		foreach ($xml->xpath('/extension/files/file') as $subExtensionNode)
		{
			$element = $this->getXMLAttribute($subExtensionNode, 'id', null);

			if (empty($element))
			{
				continue;
			}

			$type   = $this->getXMLAttribute($subExtensionNode, 'type', 'component');
			$folder = null;

			if ($type == 'plugin')
			{
				$folder = $this->getXMLAttribute($subExtensionNode, 'group', 'system');
			}

			$client = $this->getXMLAttribute($subExtensionNode, 'client', null);

			if (in_array($client, ['site', 0, '0'], true))
			{
				$client = 0;
			}
			elseif (in_array($client, ['administrator', 1, '1'], true))
			{
				$client = 1;
			}
			else
			{
				$client = null;
			}

			$this->subExtensionCriteria[] = [$type, $element, $folder, $client];
		}

	}

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
					"%s/language/%s/%s%s",
					JPATH_SITE,
					$tag,
					$langFolder,
					basename($relativePath)
				);
			}
		}

		$this->languageFiles = array_merge($this->languageFiles, $this->filterFilesArray($addons, true));
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

	/**
	 * Used to convert the found sub-extension names into actual extension objects.
	 *
	 * @param   array  $criteria
	 *
	 * @return  ExtensionInterface|null
	 * @since   1.0.0
	 */
	private function criteriaToExtension(array $criteria): ?ExtensionInterface
	{
		[$type, $element, $folder, $client] = $criteria;

		/** @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');
		/** @var DatabaseQuery $query */
		$query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = :type')
			->where($db->quoteName('element') . ' = :element')
			->bind(':type', $type, ParameterType::STRING)
			->bind(':element', $element, ParameterType::STRING);

		if ($folder)
		{
			$query
				->where($db->quoteName('folder') . ' = :folder')
				->bind(':folder', $folder, ParameterType::STRING);
		}

		if ($client)
		{
			$query
				->where($db->quoteName('client_id') . ' = :client_id')
				->bind(':client_id', $client, ParameterType::INTEGER);
		}

		try
		{
			$extensionInfo = $db->setQuery($query)->loadObject() ?? null;

			if (empty($extensionInfo))
			{
				return null;
			}
		}
		catch (\Exception $e)
		{
			return null;
		}

		return self::make($extensionInfo);
	}

}