<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Field;

defined('_JEXEC') || die;

use Akeeba\Component\Onthos\Administrator\Library\Issues\IssueManager;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Field class to list all known issues
 *
 * @since   1.0.0
 */
class IssuesField extends ListField
{
	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	protected $type = 'Issues';

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), $this->getFolders());
	}

	/**
	 * Get the distinct plugin folders from the site's database.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	private function getFolders(): array
	{
		$options = [
			HTMLHelper::_('select.option', '*', Text::_('COM_ONTHOS_ITEMS_FILTER_OPT_ISSUES_ANY')),
		];

		foreach (IssueManager::getAllKnownIssues() as $issue)
		{
			$parts     = explode('\\', $issue);
			$key       = end($parts);
			$options[] = HTMLHelper::_('select.option', $key, Text::_('COM_ONTHOS_ISSUES_LBL_' . $key));
		}

		uasort($options, fn($a, $b) => $a->text <=> $b->text);

		return $options;
	}


}