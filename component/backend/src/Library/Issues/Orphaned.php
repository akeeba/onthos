<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Helper\PackageIDsHelper;
use Akeeba\Component\Onthos\Administrator\Helper\UpdateSitesHelper;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Orphaned extension test.
 *
 * @since   1.0.0
 */
class Orphaned extends AbstractIssue implements IssueInterface
{
	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		parent::__construct($extension);

		$this->defaultSeverity = LogLevel::WARNING;
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function doTest(): bool
	{
		// Packages can never be orphans; they are by definition top-level extensions.
		if ($this->extension->type === 'package')
		{
			return false;
		}

		// If the extension has an update site it's a top-level extension, not an orphan
		if (in_array($this->extension->extension_id, UpdateSitesHelper::get()))
		{
			return false;
		}

		// If there a package ID we need to check if it's valid
		if (!empty($this->extension->package_id ?? null))
		{
			if (!in_array($this->extension->package_id, PackageIDsHelper::get()))
			{
				return true;
			}

			return false;
		}

		// At this point it's either an orphan, or a core extension (locked)
		return !$this->extension->isCore();
	}
}