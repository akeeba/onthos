<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Extension\Mixin;


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use PhpCsFixer\Cache\DirectoryInterface;

defined('_JEXEC') || die;

trait LanguageHandlingTrait
{
	/**
	 * The known languages installed on the site (front- and back-end)
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	private static array $knownLanguages = [];

	/**
	 * Possible language files.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	protected array $languageFiles = [];

	private ?string $extensionName;

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getLanguageFiles(): array
	{
		return $this->languageFiles;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	final public function getName(): ?string
	{
		if (isset($this->extensionName))
		{
			return $this->extensionName;
		}

		$this->extensionName = $this->getExtensionSlug();

		$lang      = Factory::getApplication()->getLanguage();
		$path      = $this->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;
		$extension = $this->getExtensionSlug();

		switch ($this->type)
		{
			case 'component':
				$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
				$lang->load("$extension.sys", JPATH_ADMINISTRATOR) || $lang->load("$extension.sys", $source);
				break;

			case 'library':
				$parts  = explode('/', $this->element);
				$vendor = (isset($parts[1]) ? $parts[0] : null);

				if (!$lang->load("$extension.sys", $path))
				{
					$source = $path . '/libraries/' . ($vendor ? $vendor . '/' . $parts[1] : $this->element);
					$lang->load("$extension.sys", $source);
				}
				break;

			case 'module':
				$source = $path . '/modules/' . $extension;
				$lang->load("$extension.sys", $path) || $lang->load("$extension.sys", $source);
				break;

			case 'plugin':
				$source = JPATH_PLUGINS . '/' . $this->folder . '/' . $this->element;
				$lang->load("$extension.sys", JPATH_ADMINISTRATOR) || $lang->load("$extension.sys", $source);
				break;

			case 'template':
				$source = $path . '/templates/' . $this->element;
				$lang->load("$extension.sys", $path) || $lang->load("$extension.sys", $source);
				break;

			default:
				$lang->load("$extension.sys", JPATH_SITE);
				break;
		}

		$key                 = strtoupper($this->name);
		$this->extensionName = Text::_($key);

		return $this->extensionName;
	}

	/**
	 * Returns, and optionally populates, the known installed languages on the site.
	 *
	 * This returns front- and backend languages. It does NOT return the `overrides` pseudo-tag used by Joomla's
	 * language override feature.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	protected function getKnownLanguages(): array
	{
		return self::$knownLanguages = self::$knownLanguages
			?: (function () {
				$baseDirs = [JPATH_ADMINISTRATOR, JPATH_ROOT, JPATH_API, JPATH_BASE, JPATH_PUBLIC];
				$baseDirs = array_unique($baseDirs);
				$result   = [];

				foreach ($baseDirs as $baseDir)
				{
					$path = $baseDir . DIRECTORY_SEPARATOR . 'language';

					if (!@is_dir($path))
					{
						continue;
					}

					/** @var DirectoryInterface $file */
					foreach (new \DirectoryIterator($path) as $file)
					{
						if (!$file->isDir() || $file->isDot())
						{
							continue;
						}

						if ($file->getFilename() === 'overrides')
						{
							continue;
						}

						$result[] = $file->getFilename();
					}
				}

				return array_unique($result);
			})();
	}

}