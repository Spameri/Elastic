<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class GetFieldMappingTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_getfieldmapping_test';


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
					'title' => [
						'type' => 'text',
						'fields' => [
							'keyword' => [
								'type' => 'keyword',
								'ignore_above' => 256,
							],
						],
					],
					'description' => ['type' => 'text'],
					'status' => ['type' => 'keyword'],
					'price' => ['type' => 'float'],
					'created_at' => ['type' => 'date'],
					'author' => [
						'type' => 'object',
						'properties' => [
							'name' => ['type' => 'keyword'],
							'email' => ['type' => 'keyword'],
						],
					],
				],
			],
		]);

		\usleep(100000);
	}


	public function testGetSingleFieldMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['title']);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['title']));
		\Tester\Assert::same('text', $result[self::INDEX]['mappings']['title']['mapping']['title']['type']);
	}


	public function testGetMultipleFieldMappings(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['title', 'status', 'price']);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['title']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['status']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['price']));
	}


	public function testGetSubFieldMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['title.keyword']);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['title.keyword']));
		\Tester\Assert::same('keyword', $result[self::INDEX]['mappings']['title.keyword']['mapping']['keyword']['type']);
	}


	public function testGetNestedObjectFieldMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['author.name']);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['author.name']));
		\Tester\Assert::same('keyword', $result[self::INDEX]['mappings']['author.name']['mapping']['name']['type']);
	}


	public function testGetFieldMappingWithWildcard(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['author.*']);

		\Tester\Assert::type('array', $result);
		// Should return author.name and author.email
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['author.name']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['author.email']));
	}


	public function testGetFieldMappingWithAllFieldsWildcard(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['*']);

		\Tester\Assert::type('array', $result);
		// Should return all top-level fields
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['title']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['description']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['status']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['price']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['created_at']));
	}


	public function testGetNonExistentFieldReturnsEmpty(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['nonexistent_field']);

		\Tester\Assert::type('array', $result);
		// Should return empty mappings for non-existent field
		\Tester\Assert::true(empty($result[self::INDEX]['mappings']));
	}


	public function testGetDateFieldMapping(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		$result = $getFieldMapping->execute(self::INDEX, ['created_at']);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['created_at']));
		\Tester\Assert::same('date', $result[self::INDEX]['mappings']['created_at']['mapping']['created_at']['type']);
	}


	public function testGetFieldMappingFromNonExistentIndexThrows(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetFieldMapping $getFieldMapping */
		$getFieldMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetFieldMapping::class);

		\Tester\Assert::exception(
			static fn () => $getFieldMapping->execute('nonexistent_index_xyz', ['title']),
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

(new GetFieldMappingTest())->run();
