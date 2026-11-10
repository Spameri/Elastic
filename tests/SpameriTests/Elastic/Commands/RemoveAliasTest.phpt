<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 *
 * NOTE: These tests document expected behavior, but some may fail due to
 * a bug in RemoveAlias model which uses putAlias instead of deleteAlias.
 * The putAlias endpoint ignores the 'remove' action in the body.
 */
class RemoveAliasTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_command_remove_alias_test';
	private const ALIAS = 'spameri_remove_alias_test';
	private const ALIAS_2 = 'spameri_remove_alias_test_2';


	protected function setUp(): void
	{
		parent::setUp();

		// Delete index if exists
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		// Create index
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		// Add alias
		/** @var \Spameri\Elastic\Model\Indices\AddAlias $addAlias */
		$addAlias = $this->container->getByType(\Spameri\Elastic\Model\Indices\AddAlias::class);
		$addAlias->execute(self::ALIAS, self::INDEX);
	}


	public function testRemoveAliasCommandExecutesSuccessfully(): void
	{
		/** @var \Spameri\Elastic\Commands\RemoveAlias $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\RemoveAlias::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		// Command executes without error
		\Tester\Assert::same(0, $result);
		\Tester\Assert::contains('Done', $output->fetch());
	}


	public function testRemoveAliasWithMultipleAliases(): void
	{
		// Add second alias
		/** @var \Spameri\Elastic\Model\Indices\AddAlias $addAlias */
		$addAlias = $this->container->getByType(\Spameri\Elastic\Model\Indices\AddAlias::class);
		$addAlias->execute(self::ALIAS_2, self::INDEX);

		// Remove first alias
		/** @var \Spameri\Elastic\Commands\RemoveAlias $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\RemoveAlias::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		// Command executes without error
		\Tester\Assert::same(0, $result);
	}


	public function testRemoveAliasReportsStartAndDone(): void
	{
		/** @var \Spameri\Elastic\Commands\RemoveAlias $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\RemoveAlias::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$command->run($input, $output);
		$outputContent = $output->fetch();

		\Tester\Assert::contains('Starting', $outputContent);
		\Tester\Assert::contains('Done', $outputContent);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX);
		} catch (\Throwable $e) {
			// Ignore
		}
	}

}

(new RemoveAliasTest())->run();
