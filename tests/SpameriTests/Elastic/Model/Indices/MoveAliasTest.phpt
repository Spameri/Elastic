<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class MoveAliasTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX_FROM = 'spameri_indices_movealias_from';
	private const INDEX_TO = 'spameri_indices_movealias_to';
	private const ALIAS = 'spameri_alias_test';


	protected function setUp(): void
	{
		parent::setUp();

		// Delete indexes if they exist
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX_FROM);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		try {
			$delete->execute(self::INDEX_TO);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);

		// Create source index
		$create->execute(self::INDEX_FROM, []);

		// Create target index
		$create->execute(self::INDEX_TO, []);

		\usleep(100000);

		// Add alias to source index
		/** @var \Spameri\Elastic\Model\Indices\AddAlias $addAlias */
		$addAlias = $this->container->getByType(\Spameri\Elastic\Model\Indices\AddAlias::class);
		$addAlias->execute(self::ALIAS, self::INDEX_FROM);

		\usleep(100000);
	}


	public function testMoveAliasBetweenIndexes(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\MoveAlias $moveAlias */
		$moveAlias = $this->container->getByType(\Spameri\Elastic\Model\Indices\MoveAlias::class);

		$result = $moveAlias->execute(self::ALIAS, self::INDEX_FROM, self::INDEX_TO);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true($result['acknowledged']);

		// Verify alias is now on target index
		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);

		$targetInfo = $getIndex->execute(self::INDEX_TO);
		\Tester\Assert::true(isset($targetInfo[self::INDEX_TO]['aliases'][self::ALIAS]));
	}


	public function testMoveAliasRemovesFromSourceIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\MoveAlias $moveAlias */
		$moveAlias = $this->container->getByType(\Spameri\Elastic\Model\Indices\MoveAlias::class);

		$moveAlias->execute(self::ALIAS, self::INDEX_FROM, self::INDEX_TO);

		// Verify alias is removed from source index
		/** @var \Spameri\Elastic\Model\Indices\Get $getIndex */
		$getIndex = $this->container->getByType(\Spameri\Elastic\Model\Indices\Get::class);

		$sourceInfo = $getIndex->execute(self::INDEX_FROM);
		\Tester\Assert::false(isset($sourceInfo[self::INDEX_FROM]['aliases'][self::ALIAS]));
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX_FROM);
		} catch (\Throwable $e) {
			// Ignore
		}

		try {
			$delete->execute(self::INDEX_TO);
		} catch (\Throwable $e) {
			// Ignore
		}
	}

}

(new MoveAliasTest())->run();
