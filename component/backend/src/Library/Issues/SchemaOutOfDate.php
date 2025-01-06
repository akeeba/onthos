<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Tests for an outdated database schema.
 *
 * Only if there is a corresponding #__schemas entry. Joomla! reports one or more database schema issues for the
 * extension.
 *
 * @since   1.0.0
 */
class SchemaOutOfDate extends AbstractIssue
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
	public function getDetailsTemplate(): string
	{
		return 'issues/update_schema';
	}

	/**
	 * Fix schema issues in the extension
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.0.0
	 * @see     \Joomla\Component\Installer\Administrator\Controller\DatabaseController::fix()
	 */
	protected function onFixDefault(): void
	{
		if (!$this->doTest())
		{
			return;
		}

		/** @var CMSApplication $app */
		$app = Factory::getApplication();
		/** @var MVCFactoryInterface $installerFactory */
		$installerFactory = $app->bootComponent('com_installer')->getMVCFactory();
		/** @var DatabaseModel $model */
		$model = $installerFactory->createModel('Database', 'Administrator', ['ignore_request' => true]);

		// Fix the schema
		$model->fix([$this->extension->extension_id]);

		// Purge the updates cache
		/** @var MVCFactoryInterface $jupdateFactory */
		$jupdateFactory = $app->bootComponent('com_joomlaupdate')
			->getMVCFactory();
		/** @var UpdateModel $updateModel */
		$updateModel = $jupdateFactory->createModel('Update', 'Administrator', ['ignore_request' => true]);
		$updateModel->purge();

		// Refresh the versioned assets cache
		$app->flushAssets();
	}

	/**
	 * @inheritDoc
	 * @since  1.0.0
	 */
	protected function doTest(): bool
	{
		// If the extension does not have a `#__schemas` entry the MissingTables issue is more appropriate.
		if (!$this->extension->hasSchemasEntry())
		{
			return false;
		}

		$schemaErrors = array_filter(
			$this->extension->getSchemasErrors(),
			fn(array $error) => $error['extension']?->extension_id == $this->extension->extension_id
			                    && $error['errorsCount'] > 0
		);

		return count($schemaErrors) > 0;
	}
}