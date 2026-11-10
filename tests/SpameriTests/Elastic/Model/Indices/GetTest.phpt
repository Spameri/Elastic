<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class GetTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_get_test';


	protected function setUp(): void
	{
		parent::setUp();
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, [
			'settings' => [
				'number_of_shards' => 1,
				'number_of_replicas' => 0,
			],
			'mappings' => [
				'properties' => [
					'title' => ['type' => 'text'],
				],
			],
		]);

		\usleep(100000);
	}


	public function testGetIndexInfo(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);

		$result = $get->execute(self::INDEX);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(isset($result[self::INDEX]));
	}


	public function testGetIndexReturnsSettings(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);

		$result = $get->execute(self::INDEX);

		\Tester\Assert::true(isset($result[self::INDEX]['settings']));
		\Tester\Assert::true(isset($result[self::INDEX]['settings']['index']['number_of_shards']));
	}


	public function testGetIndexReturnsMappings(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);

		$result = $get->execute(self::INDEX);

		\Tester\Assert::true(isset($result[self::INDEX]['mappings']));
		\Tester\Assert::true(isset($result[self::INDEX]['mappings']['properties']['title']));
	}


	public function testGetNonExistentIndexThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);

		\Tester\Assert::exception(
			static fn () => $get->execute('nonexistent_index_12345'),
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

(new GetTest())->run();
