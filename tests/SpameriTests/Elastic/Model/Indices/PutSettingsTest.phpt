<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class PutSettingsTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_putsettings_test';


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
			'settings' => [
				'number_of_shards' => 1,
				'number_of_replicas' => 1,
			],
		]);

		\usleep(100000);
	}


	public function testUpdateNumberOfReplicas(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		$result = $putSettings->execute(self::INDEX, [
			'number_of_replicas' => 0,
		]);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true($result['acknowledged']);

		// Verify settings were updated
		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute(self::INDEX);

		\Tester\Assert::same('0', $indexInfo[self::INDEX]['settings']['index']['number_of_replicas']);
	}


	public function testUpdateRefreshInterval(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		$result = $putSettings->execute(self::INDEX, [
			'refresh_interval' => '5s',
		]);

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute(self::INDEX);

		\Tester\Assert::same('5s', $indexInfo[self::INDEX]['settings']['index']['refresh_interval']);
	}


	public function testDisableRefreshForBulkOperations(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		// Disable refresh (for bulk indexing)
		$result = $putSettings->execute(self::INDEX, [
			'refresh_interval' => '-1',
		]);

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute(self::INDEX);

		\Tester\Assert::same('-1', $indexInfo[self::INDEX]['settings']['index']['refresh_interval']);

		// Re-enable refresh
		$putSettings->execute(self::INDEX, [
			'refresh_interval' => '1s',
		]);
	}


	public function testUpdateMaxResultWindow(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		$result = $putSettings->execute(self::INDEX, [
			'max_result_window' => 50000,
		]);

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute(self::INDEX);

		\Tester\Assert::same('50000', $indexInfo[self::INDEX]['settings']['index']['max_result_window']);
	}


	public function testUpdateMultipleSettings(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		$result = $putSettings->execute(self::INDEX, [
			'number_of_replicas' => 2,
			'refresh_interval' => '10s',
		]);

		\Tester\Assert::true($result['acknowledged']);

		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);
		$indexInfo = $getIndex->execute(self::INDEX);

		\Tester\Assert::same('2', $indexInfo[self::INDEX]['settings']['index']['number_of_replicas']);
		\Tester\Assert::same('10s', $indexInfo[self::INDEX]['settings']['index']['refresh_interval']);
	}


	public function testCannotChangeNumberOfShards(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		// number_of_shards is a static setting and cannot be changed after index creation
		\Tester\Assert::exception(
			static fn () => $putSettings->execute(self::INDEX, [
				'number_of_shards' => 5,
			]),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testPutSettingsOnClosedIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Close $close */
		$close = $this->container->getByType(\Spameri\Elastic\Model\Indices\Close::class);
		/** @var \Spameri\Elastic\Model\Indices\Open $open */
		$open = $this->container->getByType(\Spameri\Elastic\Model\Indices\Open::class);
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		// Close index
		$close->execute(self::INDEX);

		\usleep(100000);

		// Update settings on closed index should work
		$result = $putSettings->execute(self::INDEX, [
			'number_of_replicas' => 0,
		]);

		\Tester\Assert::true($result['acknowledged']);

		// Re-open index
		$open->execute(self::INDEX);
	}


	public function testPutSettingsToNonExistentIndexThrows(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\PutSettings $putSettings */
		$putSettings = $this->container->getByType(\Spameri\Elastic\Model\Indices\PutSettings::class);

		\Tester\Assert::exception(
			static fn () => $putSettings->execute('nonexistent_index_xyz', [
				'number_of_replicas' => 0,
			]),
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

(new PutSettingsTest())->run();
