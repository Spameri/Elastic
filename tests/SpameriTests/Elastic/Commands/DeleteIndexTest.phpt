<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class DeleteIndexTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_command_delete_test';
	private const INDEX_2 = 'spameri_command_delete_test_2';


	public function testDeleteIndexSuccessfully(): void
	{
		// First create the index
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		/** @var \Spameri\Elastic\Commands\DeleteIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\DeleteIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'indexName' => [self::INDEX],
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		\Tester\Assert::contains('Done', $output->fetch());

		// Verify index doesn't exist
		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);
		\Tester\Assert::false($exists->execute(self::INDEX));
	}


	public function testDeleteMultipleIndexes(): void
	{
		// Create multiple indexes
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);
		$create->execute(self::INDEX_2, []);

		/** @var \Spameri\Elastic\Commands\DeleteIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\DeleteIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'indexName' => [self::INDEX, self::INDEX_2],
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);

		// Verify both indexes don't exist
		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);
		\Tester\Assert::false($exists->execute(self::INDEX));
		\Tester\Assert::false($exists->execute(self::INDEX_2));
	}


	public function testDeleteNonExistentIndexThrowsException(): void
	{
		/** @var \Spameri\Elastic\Commands\DeleteIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\DeleteIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'indexName' => ['nonexistent_index_xyz'],
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		\Tester\Assert::exception(
			static fn () => $command->run($input, $output),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		foreach ([self::INDEX, self::INDEX_2] as $index) {
			try {
				$delete->execute($index);
			} catch (\Throwable $e) {
				// Ignore
			}
		}
	}

}

(new DeleteIndexTest())->run();
