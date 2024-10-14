<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Model\ItemModel;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use RuntimeException;

/**
 * Controller to display or apply actions to a single extension.
 *
 * @since   1.0.0
 */
class ItemController extends BaseController
{
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
					'default_view' => 'item',
					'default_task' => 'read',
				],
				$config
			),
			$factory,
			$app,
			$input
		);
	}

	/**
	 * Display the information summary of a single extension
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	public function read(): void
	{
		// Get the view
		$document   = $this->app->getDocument();
		$viewType   = $document->getType();
		$viewName   = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');
		$view       = $this->getView(
			$viewName, $viewType, '', ['base_path' => $this->basePath, 'layout' => $viewLayout]
		);

		// Set the extension ID
		$view->extension_id = max(0, intval($this->input->getInt('id', 0) ?: 0));

		// Display the view
		$this->display();
	}

	/**
	 * Fix an issue using an issue handler
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	public function fix(): void
	{
		$this->checkToken('get');

		$eid    = intval($this->input->getInt('id', 0) ?: 0);
		$issue  = $this->input->getString('issue', '');
		$action = $this->input->getString('action', 'default') ?: 'default';

		$redirectUri = $this->getRedirection();
		$message     = null;
		$messageType = null;

		try
		{
			/** @var ItemModel $model */
			$model     = $this->getModel();
			$extension = $model->getExtensionById($eid);

			if (!$extension)
			{
				throw new RuntimeException(Text::_('COM_ONTHOS_ITEM_ERR_INVALID_ID'), 404);
			}

			if (!$extension->issues->hasIssue($issue))
			{
				throw new RuntimeException(Text::_('COM_ONTHOS_ITEM_ERR_NO_SUCH_ISSUE'), 500);
			}

			$issueObject = $extension->issues->getIssue($issue);
			$issueObject->fix($action);
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
	 * Get the redirection URL.
	 *
	 * A base64-encoded URL is read from the `redirect` URL parameter. If it's missing, invalid, or not an internal URL
	 * then the HTTP Referer is used. If that's empty, or not an internal URL a hardcoded URL to the component's
	 * default view is used instead.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	private function getRedirection(): string
	{
		$referrer = $this->input->server->getString('HTTP_REFERER');

		if (\is_null($referrer) || !Uri::isInternal($referrer))
		{
			$referrer = 'index.php?option=com_onthos';
		}

		$redirect = $this->input->getBase64('redirect', base64_encode($referrer));
		try
		{
			$redirect = @base64_decode($redirect);
		}
		catch (\Exception)
		{
			$redirect = $referrer;
		}

		if (!Uri::isInternal($redirect))
		{
			$redirect = $referrer;
		}

		return $redirect;
	}
}