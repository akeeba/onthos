<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Helper\AdoptionHelper;
use Akeeba\Component\Onthos\Administrator\Helper\PackageIDsHelper;
use Akeeba\Component\Onthos\Administrator\Helper\UpdateSitesHelper;
use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Orphaned extension test.
 *
 * Non-core, non-package extensions which do not have an update site of their own and they are either lacking a parent
 * package, or claim to be owned by a non-existent package.
 *
 * @since   1.0.0
 */
class Orphaned extends AbstractIssue implements IssueInterface
{
	use AdoptionTrait;

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
		// Packages and core extensions can never be orphans.
		if ($this->extension->type === 'package' || $this->extension->isCore())
		{
			return false;
		}

		// Discovered extensions do not apply
		if ($this->extension->state == -1)
		{
			return false;
		}

		// If the extension has an update site it's a top-level extension, therefore not an orphan.
		if (in_array($this->extension->extension_id, UpdateSitesHelper::get()))
		{
			return false;
		}

		// If there is no package ID it's an orphan.
		if (empty($this->extension->package_id ?? null))
		{
			return true;
		}

		// It's an orphan only if the package ID is invalid.
		return !in_array($this->extension->package_id, PackageIDsHelper::get());
	}

	/**
	 * @inheritdoc
	 * @since  1.0.0
	 */
	public function getDetailsTemplate(): string
	{
		// Can the extension be adopted?
		if (AdoptionHelper::whichPackage($this->extension->extension_id))
		{
			return 'issues/adopt';
		}

		if (!empty($this->extension->package_id ?? null))
		{
			return 'issues/force_uninstall';
		}

		if (count($this->extension->getCanonicalUpdateServers()) && !count($this->extension->getUpdateSites()))
		{
			return 'issues/rebuild_update_site';
		}

		return 'issues/reinstall';
	}


}