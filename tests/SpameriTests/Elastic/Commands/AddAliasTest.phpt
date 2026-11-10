<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class AddAliasTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_command_alias_test';
	private const ALIAS = 'spameri_alias_test';
	private const ALIAS_2 = 'spameri_alias_test_2';


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
	}


	public function testAddAliasToIndex(): void
	{
		/** @var \Spameri\Elastic\Commands\AddAlias $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\AddAlias::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);

		// Verify alias was added by checking index info
		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute(self::INDEX);

		\Tester\Assert::true(isset($indexInfo[self::INDEX]['aliases'][self::ALIAS]));
	}


	public function testAddMultipleAliasesToIndex(): void
	{
		/** @var \Spameri\Elastic\Commands\AddAlias $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\AddAlias::class);

		// Add first alias
		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Add second alias
		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS_2,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);

		// Verify both aliases exist
		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute(self::INDEX);

		\Tester\Assert::true(isset($indexInfo[self::INDEX]['aliases'][self::ALIAS]));
		\Tester\Assert::true(isset($indexInfo[self::INDEX]['aliases'][self::ALIAS_2]));
	}


	public function testAddExistingAliasShowsError(): void
	{
		/** @var \Spameri\Elastic\Commands\AddAlias $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\AddAlias::class);

		// Add alias first time
		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Try to add same alias again
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		$outputContent = $output->fetch();
		// Error message contains "already existing alias"
		\Tester\Assert::true(
			\str_contains($outputContent, 'already existing alias') || \str_contains($outputContent, 'already exists'),
			'Output should indicate alias exists: ' . $outputContent,
		);
	}


	public function testAliasCanBeUsedToQueryIndex(): void
	{
		// Add some data to index
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);

		$clientProvider->client()->index([
			'index' => self::INDEX,
			'id' => 'test_doc',
			'body' => ['title' => 'Test via alias'],
		]);
		$clientProvider->client()->indices()->refresh(['index' => self::INDEX]);

		// Add alias
		/** @var \Spameri\Elastic\Commands\AddAlias $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\AddAlias::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'alias' => self::ALIAS,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Query using alias
		$response = $clientProvider->client()->search([
			'index' => self::ALIAS,
			'body' => [
				'query' => ['match_all' => new \stdClass()],
			],
		])->asArray();

		\Tester\Assert::same(1, $response['hits']['total']['value']);
		\Tester\Assert::same('Test via alias', $response['hits']['hits'][0]['_source']['title']);
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

(new AddAliasTest())->run();
