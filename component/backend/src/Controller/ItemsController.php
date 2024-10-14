<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Model\ItemModel;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;

/**
 * Controller to display all extensions.
 *
 * @since   1.0.0
 */
class ItemsController extends BaseController
{
	use GetRedirectionTrait;

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function __construct(
		$config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null
	)
	{
		parent::__construct(
			array_merge(
				[
					'default_view' => 'items',
					'default_task' => 'default',
				],
				$config
			),
			$factory,
			$app,
			$input
		);
	}

	/**
	 * Default task: display the list of extensions
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function default(): void
	{
		$this->display();
	}

	/**
	 * Publish an extension.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function publish(): void
	{
		$this->setField('enabled', 1);
	}

	/**
	 * Unpublish an extension.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function unpublish(): void
	{
		$this->setField('enabled', 0);
	}

	/**
	 * Protect an extension.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function protect(): void
	{
		$this->setField('protected', 1);
	}

	/**
	 * Unprotect an extension.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function unprotect(): void
	{
		$this->setField('protected', 0);
	}

	/**
	 * Lock an extension.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function lock(): void
	{
		$this->setField('locked', 1);
	}

	/**
	 * Unlock an extension.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function unlock(): void
	{
		$this->setField('locked', 0);
	}

	/**
	 * Regular extension uninstallation.
	 *
	 * Politeness level: “Please uninstall”.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function uninstall(): void
	{
		$this->doUninstall('uninstall');
	}

	/**
	 * Remove the installation script, then uninstall.
	 *
	 * Politeness level: “I hope you uninstall”.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function noscript(): void
	{
		$this->doUninstall('uninstallNoScript');
	}

	/**
	 * Forcibly uninstall the extension.
	 *
	 * Politeness level: “I wasn't asking”.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function forced(): void
	{
		$this->doUninstall('uninstallForced');
	}

	/**
	 * Remove the extension record.
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function unrecord(): void
	{
		$this->doUninstall('removeRecord');
	}

	/**
	 * Perform the actual uninstallation.
	 *
	 * @param   string  $method  Which uninstallation method should I use?
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function doUninstall(string $method = 'uninstall'): void
	{
		$this->checkToken('post');

		$cid           = (array) $this->input->get('cid', [], 'int');
		$cid           = ArrayHelper::toInteger(array_filter($cid));
		$redirectUri   = $this->getRedirection();
		$messageType   = null;
		$numExtensions = 0;

		try
		{
			/** @var ItemModel $model */
			$model = $this->getModel();

			foreach ($cid as $eid)
			{
				$extension = $model->getExtensionById($eid);

				if (!$extension)
				{
					throw new RuntimeException(Text::_('COM_ONTHOS_ITEM_ERR_INVALID_ID'), 404);
				}

				$model->{$method}($extension);
			}

			$message = Text::plural('COM_ONTHOS_ITEMS_LBL_UNINSTALLED_N', $numExtensions);
		}
		catch (\Throwable $e)
		{
			$message     = $e->getMessage();
			$messageType = 'error';
		}
		finally
		{
			$this->setRedirect($redirectUri, $message, $messageType);
		}
	}

	/**
	 * Sets the specified field for a collection of extensions based on the IDs selected in the UI.
	 *
	 * @param   string  $fieldName  The name of the field to set.
	 * @param   int     $value      The value to set for the field.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function setField(string $fieldName, int $value): void
	{
		$this->checkToken('post');

		$cid           = (array) $this->input->get('cid', [], 'int');
		$cid           = ArrayHelper::toInteger(array_filter($cid));
		$redirectUri   = $this->getRedirection();
		$messageType   = null;
		$numExtensions = 0;

		try
		{
			/** @var ItemModel $model */
			$model = $this->getModel();

			foreach ($cid as $eid)
			{
				$extension = $model->getExtensionById($eid);

				if (!$extension)
				{
					throw new RuntimeException(Text::_('COM_ONTHOS_ITEM_ERR_INVALID_ID'), 404);
				}

				if ($extension->setFieldName($fieldName, $value))
				{
					$numExtensions++;
				}
			}

			$message = Text::plural(
				'COM_ONTHOS_ITEM_LBL_' . $fieldName . '_' . ($value ? 'SET' : 'UNSET') . '_N', $numExtensions
			);
		}
		catch (\Throwable $e)
		{
			$message     = $e->getMessage();
			$messageType = 'error';
		}
		finally
		{
			$this->setRedirect($redirectUri, $message, $messageType);
		}
	}
}