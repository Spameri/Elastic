<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class PutMappingTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_putmapping_test';


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

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, [
			'mappings' => [
				'properties' => [
					'title' => ['type' => 'text'],
				],
			],
		]);

		\usleep(100000);
	}


	public function testAddNewFieldToMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		$mapping = [
			'properties' => [
				'description' => ['type' => 'text'],
				'status' => ['type' => 'keyword'],
			],
		];

		$result = $putMapping->execute(self::INDEX, $mapping);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true($result['acknowledged']);

		// Verify mapping was updated
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		$indexMapping = $getMapping->execute(self::INDEX);

		// Original field should still exist
		\Tester\Assert::true(isset($indexMapping[self::INDEX]['mappings']['properties']['title']));
		// New fields should be added
		\Tester\Assert::true(isset($indexMapping[self::INDEX]['mappings']['properties']['description']));
		\Tester\Assert::true(isset($indexMapping[self::INDEX]['mappings']['properties']['status']));
		\Tester\Assert::same('keyword', $indexMapping[self::INDEX]['mappings']['properties']['status']['type']);
	}


	public function testAddComplexFieldToMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		$mapping = [
			'properties' => [
				'name' => [
					'type' => 'text',
					'fields' => [
						'keyword' => [
							'type' => 'keyword',
							'ignore_above' => 256,
						],
					],
				],
			],
		];

		$result = $putMapping->execute(self::INDEX, $mapping);

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		$indexMapping = $getMapping->execute(self::INDEX);

		\Tester\Assert::true(isset($indexMapping[self::INDEX]['mappings']['properties']['name']));
		\Tester\Assert::same('text', $indexMapping[self::INDEX]['mappings']['properties']['name']['type']);
		\Tester\Assert::true(isset($indexMapping[self::INDEX]['mappings']['properties']['name']['fields']['keyword']));
	}


	public function testAddNestedObjectToMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		$mapping = [
			'properties' => [
				'author' => [
					'type' => 'object',
					'properties' => [
						'name' => ['type' => 'keyword'],
						'email' => ['type' => 'keyword'],
					],
				],
			],
		];

		$result = $putMapping->execute(self::INDEX, $mapping);

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		$indexMapping = $getMapping->execute(self::INDEX);

		\Tester\Assert::true(isset($indexMapping[self::INDEX]['mappings']['properties']['author']));
		\Tester\Assert::true(isset($indexMapping[self::INDEX]['mappings']['properties']['author']['properties']['name']));
		\Tester\Assert::same('keyword', $indexMapping[self::INDEX]['mappings']['properties']['author']['properties']['name']['type']);
	}


	public function testMappingConflictThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		// Try to change the type of an existing field (title is text, trying to make it keyword)
		$mapping = [
			'properties' => [
				'title' => ['type' => 'keyword'],
			],
		];

		\Tester\Assert::exception(
			static fn () => $putMapping->execute(self::INDEX, $mapping),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testPutMappingWithDynamicStrict(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		$mapping = [
			'properties' => [
				'category' => ['type' => 'keyword'],
			],
		];

		$result = $putMapping->execute(self::INDEX, $mapping, 'strict');

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		$indexMapping = $getMapping->execute(self::INDEX);

		\Tester\Assert::same('strict', $indexMapping[self::INDEX]['mappings']['dynamic']);
	}


	public function testPutMappingWithDynamicTrue(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		$mapping = [
			'properties' => [
				'tags' => ['type' => 'keyword'],
			],
		];

		$result = $putMapping->execute(self::INDEX, $mapping, 'true');

		\Tester\Assert::true($result['acknowledged']);
	}


	public function testAddDateFieldToMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		$mapping = [
			'properties' => [
				'created_at' => [
					'type' => 'date',
					'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis',
				],
			],
		];

		$result = $putMapping->execute(self::INDEX, $mapping);

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		$indexMapping = $getMapping->execute(self::INDEX);

		\Tester\Assert::same('date', $indexMapping[self::INDEX]['mappings']['properties']['created_at']['type']);
	}


	public function testPutMappingToNonExistentIndexThrows(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutMapping $putMapping */
		$putMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutMapping::class);

		$mapping = [
			'properties' => [
				'field' => ['type' => 'keyword'],
			],
		];

		\Tester\Assert::exception(
			static fn () => $putMapping->execute('nonexistent_index_xyz', $mapping),
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

(new PutMappingTest())->run();
