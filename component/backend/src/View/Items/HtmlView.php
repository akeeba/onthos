<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\View\Items;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Akeeba\Component\Onthos\Administrator\Mixin\ViewLoadAnyTemplateTrait;
use Akeeba\Component\Onthos\Administrator\Model\ItemsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View controller for displaying a list of extensions
 *
 * @since   1.0.0
 */
class HtmlView extends BaseHtmlView
{
	use ViewLoadAnyTemplateTrait;

	/**
	 * The search tools form
	 *
	 * @var    Form
	 * @since  1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	public array $activeFilters = [];

	/**
	 * An array of items
	 *
	 * @var    array<ExtensionInterface>
	 * @since  1.0.0
	 */
	protected array $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.0.0
	 */
	protected Pagination $pagination;

	/**
	 * The model state
	 *
	 * @var    Registry
	 * @since  1.0.0
	 */
	protected Registry $state;

	/**
	 * Does the listing include core extensions which have unreliable XML manifessts?
	 *
	 * @var  bool
	 */
	protected bool $isUnreliableCore = false;

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function display($tpl = null)
	{
		/** @var ItemsModel $model */
		$model                  = $this->getModel();
		$this->items            = $model->getItems();
		$this->pagination       = $model->getPagination();
		$this->state            = $model->getState();
		$this->filterForm       = $model->getFilterForm();
		$this->activeFilters    = $model->getActiveFilters();
		$this->isUnreliableCore = array_reduce(
			$this->items,
			fn(bool $carry, ExtensionInterface $extension) => $carry || $extension->isCore(),
			false
		);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Set up the table column auto-hiding.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function tableColumnsAutohide(): void
	{
		try
		{
			$this->getDocument()->getWebAssetManager()->useScript('table.columns');
		}
		catch (\Throwable $e)
		{
			// This might indeed fail on old Joomla! versions.
		}
	}

	/**
	 * Set up the table rows multi-select.
	 *
	 * @param   string|null  $tableSelector  CSS selector for the table
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function tableRowsMultiselect(?string $tableSelector = null): void
	{
		try
		{
			$this->getDocument()->getWebAssetManager()->useScript('multiselect');

			if (empty($tableSelector))
			{
				return;
			}

			$this->getDocument()->addScriptOptions(
				'js-multiselect', [
					'formName' => $tableSelector,
				]
			);
		}
		catch (\Throwable $e)
		{
			// This might indeed fail on old Joomla! versions.
		}
	}

	/**
	 * Get the icon for the extension's type.
	 *
	 * @param   ExtensionInterface  $item  The item to get the icon for.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	protected function getTypeIcon(ExtensionInterface $item): string
	{
		return match ($item->type)
		{
			'component' => 'fa-puzzle-piece',
			'file' => 'fa-file-alt',
			'library' => 'fa-book',
			default => 'fa-boxes-packing',
			'plugin' => 'fa-plug',
			'module' => 'fa-cube',
			'template' => 'fa-paint-brush',
		};
	}

	/**
	 * Get the icon for the extension's client ID.
	 *
	 * @param   ExtensionInterface  $item  The item to get the icon for.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	protected function getApplicationIcon(ExtensionInterface $item): string
	{
		return match ($item->client_id)
		{
			default => 'fa-globe',
			1 => 'fa-black-tie',
			3 => 'fa-code',
		};
	}

	/**
	 * Get the human-readable name for the extension's client ID.
	 *
	 * @param   ExtensionInterface  $item  The item to get the human-readable name for.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	protected function getApplicationName(ExtensionInterface $item): string
	{
		return match ($item->client_id)
		{
			default => Text::_('JSite'),
			1 => Text::_('JAdministrator'),
			3 => Text::_('JAPI'),
		};
	}

	/**
	 * Populates the toolbar
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.0.0
	 */
	protected function addToolbar(): void
	{
		/** @var Toolbar $toolbar */
		$toolbar = Factory::getApplication()->getDocument()->getToolbar('toolbar');

		ToolbarHelper::preferences('com_onthos');

		ToolbarHelper::title(Text::_('COM_ONTHOS'), 'fa fa-poo-storm');
	}
}