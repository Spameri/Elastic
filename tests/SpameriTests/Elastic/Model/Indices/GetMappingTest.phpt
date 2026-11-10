<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class GetMappingTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_getmapping_test';


	protected function setUp(): void
	{
		parent::setUp();
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, [
			'mappings' => [
				'properties' => [
					'title' => ['type' => 'text'],
					'status' => ['type' => 'keyword'],
					'count' => ['type' => 'integer'],
					'price' => ['type' => 'float'],
					'active' => ['type' => 'boolean'],
					'created' => ['type' => 'date'],
				],
			],
		]);

		\usleep(100000);
	}


	public function testRetrieveFullMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);

		$result = $getMapping->execute(self::INDEX);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['properties']));
	}


	public function testFieldTypesCorrect(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);

		$result = $getMapping->execute(self::INDEX);
		$properties = $result[self::INDEX]['mappings']['properties'];

		\Tester\Assert::same('text', $properties['title']['type']);
		\Tester\Assert::same('keyword', $properties['status']['type']);
		\Tester\Assert::same('integer', $properties['count']['type']);
		\Tester\Assert::same('float', $properties['price']['type']);
		\Tester\Assert::same('boolean', $properties['active']['type']);
		\Tester\Assert::same('date', $properties['created']['type']);
	}


	public function testGetMappingNonExistentThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);

		\Tester\Assert::exception(
			static fn () => $getMapping->execute('nonexistent_index_12345'),
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

(new GetMappingTest())->run();
