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
	 * @var   array<IssueInterface>
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
	 * Retrieves an array of all known issues which can affect a managed extension.
	 *
	 * @return  array  An array containing the FQNs of classes that represent known issues.
	 * @since   1.0.0
	 */
	public static function getAllKnownIssues(): array
	{
		return [
			CoreForceDisabled::class,
			Leftover::class,
			PartiallyInstalled::class,
			MissingChildren::class,
			NonCoreProtected::class,
			Orphaned::class,
			WrongParent::class,
			NonCoreLocked::class,
			NoScript::class,
			MissingLanguage::class,
			MissingMedia::class,
		];
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
		return new self(self::getAllKnownIssues(), $extension);
	}

	/**
	 * Retrieves the list of issues affecting the managed extension.
	 *
	 * @return  array<IssueInterface>  An array of issue classes the extension is affected by.
	 * @since   1.0.0
	 */
	public function getIssues(): array
	{
		return $this->issues;
	}

	/**
	 * Checks if the managed extension is affected by the specified issue.
	 *
	 * The issue to check can be one of the following:
	 * - The FQN of a class implementing IssueInterface.
	 * - The last namespace part of any issue classname known to this manager, regardless of capitalisation.
	 *
	 * @param   string  $issueSlug  The issue to check.
	 *
	 * @return  bool  True if the issue exists, false otherwise.
	 * @since   1.0.0
	 */
	public function hasIssue(string $issueSlug): bool
	{
		if (str_contains($issueSlug, '\\'))
		{
			$parts = explode('\\', $issueSlug);
			$issueSlug = array_pop($parts);
		}

		$issueSlug = strtolower($issueSlug);

		return array_reduce(
			$this->issues,
			function (bool $carry, IssueInterface $currentItem) use ($issueSlug)
			{
				if ($carry)
				{
					return true;
				}

				$parts = explode('\\', get_class($currentItem));

				return $issueSlug === strtolower(array_pop($parts));
			},
			false
		);
	}

	/**
	 * Get the issue object for an issue affecting the managed extension.
	 *
	 * @param   string  $issueSlug  The issue to retrieve. Must be the FQN of a class implementing IssueInterface.
	 *
	 * @return  IssueInterface|null  The issue object. NULL if not found / not affected.
	 * @since   1.0.0
	 */
	public function getIssue(string $issueSlug): ?IssueInterface
	{
		if (str_contains($issueSlug, '\\'))
		{
			$parts = explode('\\', $issueSlug);
			$issueSlug = array_pop($parts);
		}

		$issueSlug = strtolower($issueSlug);

		foreach ($this->issues as $currentItem)
		{
			$parts = explode('\\', get_class($currentItem));

			if ($issueSlug === strtolower(array_pop($parts)))
			{
				return $issueSlug;
			}
		}

		return null;
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
			$issue = new $possibleIssueToCheck($this->extension);

			if ($issue())
			{
				$this->issues[] = $issue;
			}
		}
	}
}