<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class CreateIndexTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_command_create_test';


	protected function setUp(): void
	{
		parent::setUp();
		$this->cleanupIndex();
	}


	private function cleanupIndex(): void
	{
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);

		try {
			// Delete by pattern (index name + timestamp variations)
			$clientProvider->client()->indices()->delete(['index' => self::INDEX . '*']);
		} catch (\Throwable $e) {
			// Ignore - index may not exist
		}
	}


	public function testCreateIndexSuccessfully(): void
	{
		/** @var \Spameri\Elastic\Commands\CreateIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\CreateIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'indexName' => self::INDEX,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		\Tester\Assert::contains('Index ' . self::INDEX . ' created.', $output->fetch());

		// Verify index exists
		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);
		\Tester\Assert::true($exists->execute(self::INDEX));
	}


	public function testCreateExistingIndexShowsError(): void
	{
		// First create the index via command
		/** @var \Spameri\Elastic\Commands\CreateIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\CreateIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'indexName' => self::INDEX,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Now try via command again - should show error about existing
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		// Should contain error message about existing index
		$outputContent = $output->fetch();
		\Tester\Assert::true(
			\str_contains($outputContent, 'already exists') || \str_contains($outputContent, 'You are trying to create'),
			'Output should contain error about existing index: ' . $outputContent,
		);
	}


	public function testCreateWithForceDeletesExisting(): void
	{
		// First create the index via command
		/** @var \Spameri\Elastic\Commands\CreateIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\CreateIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'indexName' => self::INDEX,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Verify index was created
		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);
		\Tester\Assert::true($exists->execute(self::INDEX));

		// Now force create via command - should delete and recreate
		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'indexName' => self::INDEX,
			'--force' => true,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		$outputContent = $output->fetch();
		\Tester\Assert::contains('Index ' . self::INDEX . ' deleted.', $outputContent);
		\Tester\Assert::contains('Index ' . self::INDEX . ' created.', $outputContent);

		// Verify index still exists
		\Tester\Assert::true($exists->execute(self::INDEX));
	}


	protected function tearDown(): void
	{
		$this->cleanupIndex();
	}

}

(new CreateIndexTest())->run();
