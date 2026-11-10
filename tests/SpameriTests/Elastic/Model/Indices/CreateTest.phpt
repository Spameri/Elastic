<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class CreateTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_create_test';


	public function testCreateIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);

		$result = $create->execute(self::INDEX, []);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true($result['acknowledged']);

		// Verify it exists
		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);
		\Tester\Assert::true($exists->execute(self::INDEX));
	}


	public function testCreateIndexWithMapping(): void
	{
		$indexName = self::INDEX . '_mapping';

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);

		$mapping = [
			'mappings' => [
				'properties' => [
					'title' => ['type' => 'text'],
					'status' => ['type' => 'keyword'],
				],
			],
		];

		$result = $create->execute($indexName, $mapping);

		\Tester\Assert::true($result['acknowledged']);

		// Verify mapping was applied
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		$indexMapping = $getMapping->execute($indexName);

		\Tester\Assert::true(isset($indexMapping[$indexName]['mappings']['properties']['title']));
		\Tester\Assert::same('text', $indexMapping[$indexName]['mappings']['properties']['title']['type']);

		// Cleanup
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$delete->execute($indexName);
	}


	public function testCreateIndexWithSettings(): void
	{
		$indexName = self::INDEX . '_settings';

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);

		$settings = [
			'settings' => [
				'number_of_shards' => 1,
				'number_of_replicas' => 0,
			],
		];

		$result = $create->execute($indexName, $settings);

		\Tester\Assert::true($result['acknowledged']);

		// Verify settings were applied
		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute($indexName);

		\Tester\Assert::same('1', $indexInfo[$indexName]['settings']['index']['number_of_shards']);
		\Tester\Assert::same('0', $indexInfo[$indexName]['settings']['index']['number_of_replicas']);

		// Cleanup
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$delete->execute($indexName);
	}


	public function testCreateAlreadyExistingThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);

		// Create index first time
		$create->execute(self::INDEX, []);

		// Try to create again - should throw
		\Tester\Assert::exception(
			static fn () => $create->execute(self::INDEX, []),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
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

(new CreateTest())->run();
