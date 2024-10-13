<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;

use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;
use Joomla\CMS\Language\Text;
use Psr\Log\LogLevel;

defined('_JEXEC') || die;

/**
 * Abstract implementation of an extension issue test.
 *
 * @since  1.0.0
 */
abstract class AbstractIssue implements IssueInterface
{
	/**
	 * The extension we are testing.
	 *
	 * @var   ExtensionInterface
	 * @since 1.0.0
	 */
	protected ExtensionInterface $extension;

	/**
	 * The default severity level for this issue.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	protected string $defaultSeverity = LogLevel::INFO;

	/**
	 * Should the test result be cached?
	 *
	 * @var   bool
	 * @since 1.0.0
	 */
	protected bool $cacheResult = true;

	/**
	 * The last result running this test.
	 *
	 * @var   bool
	 * @since 1.0.0
	 */
	protected bool $result;

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function __construct(ExtensionInterface $extension)
	{
		$this->extension = $extension;
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	final public function __invoke(): bool
	{
		if ($this->cacheResult)
		{
			return $this->result ??= $this->doTest();
		}

		return $this->result = $this->doTest();
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function getIcon(): string
	{
		return Text::_('COM_ONTHOS_ISSUES_ICON_' . $this->getSlug());
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function getLabel(): string
	{
		return Text::_('COM_ONTHOS_ISSUES_LBL_' . $this->getSlug());
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function getDescription(): string
	{
		return Text::_('COM_ONTHOS_ISSUES_DESCRIPTION_' . $this->getSlug());
	}

	/**
	 * @inheritdoc
	 * @since 1.0.0
	 */
	public function getSeverity(): string
	{
		return $this->defaultSeverity;
	}

	/**
	 * Performs the test described by this class.
	 *
	 * @return  bool
	 * @since   1.0.0
	 */
	abstract protected function doTest(): bool;

	/**
	 * Return the slug for this issue.
	 *
	 * This is the last part of the FQN in all lowercase.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	protected function getSlug(): string
	{
		$parts = explode('\\', static::class);

		return strtolower(end($parts));
	}
}