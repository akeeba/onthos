<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Helper\AdoptionHelper;

/**
 * Trait to allow packages adopt extensions with no, or invalid, package IDs.
 *
 * @since 1.0.0
 */
trait AdoptionTrait
{
	/**
	 * Have the correct package adopt the extension.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	protected function onFixAdopt(): void
	{
		$package = AdoptionHelper::whichPackage($this->extension->extension_id);

		if ($package === null)
		{
			return;
		}

		$this->extension->setFieldName('package_id', $package->extension_id);
	}
}