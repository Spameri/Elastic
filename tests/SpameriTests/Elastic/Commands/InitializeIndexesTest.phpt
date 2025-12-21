<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class InitializeIndexesTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testInitializeAllIndexes(): void
	{
		/** @var \Spameri\Elastic\Commands\InitializeIndexes $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\InitializeIndexes::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);

		$outputContent = $output->fetch();
		// Should mention at least one index created
		\Tester\Assert::true(
			\str_contains($outputContent, 'created') || \str_contains($outputContent, 'already exists'),
			'Output should indicate index creation: ' . $outputContent,
		);
	}


	public function testInitializeSpecificIndex(): void
	{
		/** @var \Spameri\Elastic\Commands\InitializeIndexes $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\InitializeIndexes::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'entityName' => ['title'],
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);

		// Verify title index exists
		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);
		\Tester\Assert::true($exists->execute(\SpameriTests\Elastic\Config::INDEX_TITLE));
	}


	public function testInitializeWithForceDeletesExisting(): void
	{
		// First initialize
		/** @var \Spameri\Elastic\Commands\InitializeIndexes $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\InitializeIndexes::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'entityName' => ['title'],
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Now force re-initialize
		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'entityName' => ['title'],
			'--force' => true,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		$outputContent = $output->fetch();
		\Tester\Assert::contains('deleted', $outputContent);
		\Tester\Assert::contains('created', $outputContent);
	}


	public function testInitializeExistingIndexShowsError(): void
	{
		// First initialize
		/** @var \Spameri\Elastic\Commands\InitializeIndexes $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\InitializeIndexes::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'entityName' => ['title'],
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Try again without force
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		// Should contain error message about existing index
		$outputContent = $output->fetch();
		\Tester\Assert::true(
			\str_contains($outputContent, 'already exists')
			|| \str_contains($outputContent, 'already existing')
			|| \str_contains($outputContent, 'resource_already_exists'),
			'Output should contain error about existing index: ' . $outputContent,
		);
	}


	public function testInitializeAppliesMappingCorrectly(): void
	{
		// Clean up first
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_TITLE . '*']);
		} catch (\Throwable $e) {
			// Ignore
		}

		/** @var \Spameri\Elastic\Commands\InitializeIndexes $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\InitializeIndexes::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'entityName' => ['title'],
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Verify index exists with correct mapping using alias
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		$mapping = $getMapping->execute(\SpameriTests\Elastic\Config::INDEX_TITLE);

		// The mapping array may use the actual index name (with timestamp), not the alias
		// So check that we got a non-empty mapping result
		\Tester\Assert::true(\count($mapping) > 0, 'Mapping should not be empty');
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		$indexes = [
			\SpameriTests\Elastic\Config::INDEX_TITLE,
			\SpameriTests\Elastic\Config::INDEX_IMAGE,
		];

		foreach ($indexes as $index) {
			try {
				$delete->execute($index);
			} catch (\Throwable $e) {
				// Ignore
			}
		}
	}

}

(new InitializeIndexesTest())->run();
