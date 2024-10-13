<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Library\Issues;


use Akeeba\Component\Onthos\Administrator\Library\Extension\ExtensionInterface;

defined('_JEXEC') || die;

/**
 * Manages the tests for discovered extension issues.
 *
 * @since   1.0.0
 */
final class IssueManager
{
	/**
	 * List of known issues affecting the extension managed by the Manager
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	private array $issues = [];

	/**
	 * Class constructor.
	 *
	 * The $possibleIssuesToCheck array must contain the FQNs of classes implementing IssueInterface.
	 *
	 * @param   array               $possibleIssuesToCheck  Array of possible issues to check.
	 * @param   ExtensionInterface  $extension              An instance of ExtensionInterface.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function __construct(private array $possibleIssuesToCheck, private ExtensionInterface $extension)
	{
		$this->initialiseIssues();
	}

	/**
	 * Factory method to create a new instance of the class with predefined known issues.
	 *
	 * @param   ExtensionInterface  $extension  An instance of ExtensionInterface.
	 *
	 * @return  self  Returns a new instance of the class populated with known issues, and the provided extension.
	 * @since   1.0.0
	 */
	public static function make(ExtensionInterface $extension): self
	{
		$knownIssues = [
			Broken::class,
			CoreForceDisabled::class,
			MissingChildren::class,
			MissingLanguage::class,
			NonCoreLocked::class,
			NonCoreProtected::class,
			Orphaned::class,
		];

		return new self($knownIssues, $extension);
	}

	/**
	 * Retrieves the list of issues affecting the managed extension.
	 *
	 * @return  array<string>  An array of issue classes the extension is affected by.
	 * @since   1.0.0
	 */
	public function getIssues(): array
	{
		return $this->issues;
	}

	/**
	 * Checks if the managed extension is affected by the specified issue.
	 *
	 * @param   string  $issue  The issue to check. Must be the FQN of a class implementing IssueInterface.
	 *
	 * @return  bool  True if the issue exists, false otherwise.
	 * @since   1.0.0
	 */
	public function hasIssue(string $issue): bool
	{
		return in_array($issue, $this->issues);
	}

	/**
	 * Get the issue object for an issue affecting the managed extension.
	 *
	 * @param   string  $issue  The issue to retrieve. Must be the FQN of a class implementing IssueInterface.
	 *
	 * @return  IssueInterface|null  The issue object. NULL if not found / not affected.
	 * @since   1.0.0
	 */
	public function getIssue(string $issue): ?IssueInterface
	{
		if (!$this->hasIssue($issue))
		{
			return null;
		}

		return new $issue();
	}

	/**
	 * Checks if the managed extension is affected by any of the possible issues to check.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function initialiseIssues(): void
	{
		$this->issues = [];

		foreach ($this->possibleIssuesToCheck as $possibleIssueToCheck)
		{
			if (
				!class_exists($possibleIssueToCheck)
				|| !class_implements($possibleIssueToCheck, IssueInterface::class))
			{
				continue;
			}

			/** @var IssueInterface $issue */
			$issue = new $possibleIssueToCheck();

			if ($issue($this->extension))
			{
				$this->issues[] = $possibleIssueToCheck;
			}
		}
	}
}