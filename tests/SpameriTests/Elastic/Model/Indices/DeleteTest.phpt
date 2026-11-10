<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class DeleteTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_delete_test';


	public function testDeleteExistingIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);
		\Tester\Assert::true($exists->execute(self::INDEX));

		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$result = $delete->execute(self::INDEX);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true($result['acknowledged']);

		// Verify it's gone
		\Tester\Assert::false($exists->execute(self::INDEX));
	}


	public function testDeleteNonExistentThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		\Tester\Assert::exception(
			static fn () => $delete->execute('nonexistent_index_12345'),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testDeleteReturnsAcknowledged(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$result = $delete->execute(self::INDEX);

		\Tester\Assert::true(isset($result['acknowledged']));
		\Tester\Assert::true($result['acknowledged']);
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

(new DeleteTest())->run();
