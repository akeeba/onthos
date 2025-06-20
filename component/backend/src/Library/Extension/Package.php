<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
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
	 * All subextensions of this package, including NULL values from missing subextensions.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	private array $subExtensions;

	/**
	 * Get all subextensions, including those not installed, with metadata.
	 *
	 * @return  array<object{type: null|string, element: string, folder: null|string, client_id: null|int, extension:
	 *                             null|ExtensionInterface, installed: bool}>
	 * @since   1.0.0
	 */
	public function getSubextensionsWithMeta(): array
	{
		$this->populateSubExtensions();

		if (empty($this->subExtensions))
		{
			return [];
		}

		$ret = [];

		for ($i = 0; $i < count($this->subExtensions); $i++)
		{
			$o            = (object) [
				'type'      => $this->subExtensionCriteria[$i][0],
				'element'   => $this->subExtensionCriteria[$i][1],
				'folder'    => $this->subExtensionCriteria[$i][2],
				'client_id' => $this->subExtensionCriteria[$i][3],
			];
			$o->extension = $this->subExtensions[$i];
			$o->installed = !empty($o->extension);
			$ret[]        = $o;
		}

		return $ret;
	}

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
		$this->populateSubExtensions();

		return array_filter($this->subExtensions);
	}

	/**
	 * Is this package missing some of its extensions altogether?
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function hasMissingSubextensions(): bool
	{
		$this->populateSubExtensions();

		return !empty(array_filter($this->subExtensions, fn(?ExtensionInterface $extension) => empty($extension)));
	}

	public function hasInvalidChildren(): bool
	{
		$this->populateSubExtensions();

		$subextensionIDs = array_map(
			fn(ExtensionInterface $extension) => $extension->extension_id,
			array_filter($this->subExtensions)
		);
		$invalidChildren = array_filter(
			$this->getChildrenExtensionsInDatabase(),
			fn(ExtensionInterface $extension) => !in_array($extension->extension_id, $subextensionIDs)
		);

		return count($invalidChildren) > 0;
	}

	/**
	 * Do some extensions of this package have no, or the wrong package ID?
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	public function hasExtensionsToAdopt(): bool
	{
		$this->populateSubExtensions();

		$extensions = array_filter($this->subExtensions);

		return array_reduce(
			$extensions,
			fn(bool $carry, ?ExtensionInterface $extension) => $carry || $extension?->package_id != $this->extension_id,
			false
		);
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function onAfterManifestFound(SimpleXMLElement $xml)
	{
		$this->subExtensionCriteria = [];

		foreach ($xml->xpath('/extension/files/*') as $subExtensionNode)
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

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	protected function populateDefaultExtensionPaths(): void
	{
		// Packages do not install files of their own.
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
	protected function populateLanguagesFromManifest(SimpleXMLElement $xml): void
	{
		$this->languageFiles = [];

		foreach ($xml->xpath('/extension/languages') as $siteLangContainer)
		{
			$langFolder = $this->getXMLAttribute($siteLangContainer, 'folder', '');
			$langFolder .= empty($langFolder) ? '' : '/';

			foreach ($siteLangContainer->children() as $node)
			{
				$tag          = $this->getXMLAttribute($node, 'tag', 'en-GB');
				$relativePath = (string) $node;

				$this->addAlternativeLanguageFiles(
					'site',
					sprintf(
						"%s/language/%s/%s",
						JPATH_SITE,
						$tag,
						basename($relativePath)
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
		$bareName = str_starts_with($this->element, 'pkg_') ? substr($this->element, 4) : $this->element;

		return dirname($this->manifestPath) . '/' . $bareName . '/' . $fileName;
	}

	/**
	 * Utility method to populate the package's sub-extension objects.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function populateSubExtensions(): void
	{
		$this->subExtensions ??= array_map([$this, 'criteriaToExtension'], $this->subExtensionCriteria);
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

	/**
	 * Returns all extensions claiming to be subextensions of this package, as per the `#__extensions` table records.
	 *
	 * @return  array<Extension>
	 * @since   1.0.0
	 */
	private function getChildrenExtensionsInDatabase(): array
	{
		/** @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$eid   = $this->extension_id;
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('parent_id') . ' = :parent_id')
			->bind(':parent_id', $eid, ParameterType::INTEGER);

		try
		{
			$extensionInfo = $db->setQuery($query)->loadObject() ?? null;

			if (empty($extensionInfo))
			{
				return [];
			}
		}
		catch (\Exception $e)
		{
			return [];
		}

		return array_map([self::class, 'make'], $extensionInfo);
	}

}