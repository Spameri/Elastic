<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\IdentityMap;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class MarkInsertedTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Model\IdentityMap $identityMap;


	protected function setUp(): void
	{
		$this->identityMap = new \Spameri\Elastic\Model\IdentityMap();
	}


	public function testMarkInsertedAddsEntityToMap(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);

		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id');
		\Tester\Assert::same($entity, $retrieved);
	}


	public function testMarkInsertedTracksPersistedHash(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);

		\Tester\Assert::true(isset($this->identityMap->persisted[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id']));
	}


	public function testIsChangedReturnsTrueForNewEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		// Not marked inserted yet
		\Tester\Assert::true($this->identityMap->isChanged($entity));
	}


	public function testIsChangedReturnsFalseForUnchangedEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);

		// Not changed since insert
		\Tester\Assert::false($this->identityMap->isChanged($entity));
	}


	public function testIsChangedReturnsTrueForModifiedEntity(): void
	{
		$backdrop = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('image-id'),
			null,
		);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);

		// Modify the entity
		$entity->backdrop = $backdrop;

		// Should detect change
		\Tester\Assert::true($this->identityMap->isChanged($entity));
	}


	public function testMultipleInsertsUpdateHash(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);
		$hash1 = $this->identityMap->persisted[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id'];

		// Modify entity
		$backdrop = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('image-id'),
			null,
		);
		$entity->backdrop = $backdrop;

		// Re-insert should update hash
		$this->identityMap->markInserted($entity);
		$hash2 = $this->identityMap->persisted[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id'];

		\Tester\Assert::notEqual($hash1, $hash2);
	}

}

(new MarkInsertedTest())->run();
