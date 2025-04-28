<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library;


defined('_JEXEC') || die;

/**
 * A link to an internal Joomla extension management page.
 *
 * @property-read string $url   URL to the management page.
 * @property-read string $label The label of the link.
 * @property-read string $icon  The icon for the link.
 *
 * @since   1.0.0
 */
class ManagementLink
{
	public function __construct(private string $url, private string $label, private string $icon = 'fa fa-link') {}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getIcon(): string
	{
		return $this->icon;
	}

	public function __get(string $name)
	{
		return match ($name)
		{
			'url' => $this->getUrl(),
			'label' => $this->getLabel(),
			'icon' => $this->getIcon(),
			default => null,
		};
	}
}