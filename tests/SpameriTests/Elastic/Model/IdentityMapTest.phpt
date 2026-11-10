<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class IdentityMapTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Model\IdentityMap $identityMap;


	protected function setUp(): void
	{
		$this->identityMap = new \Spameri\Elastic\Model\IdentityMap();
	}


	public function testAddAndGetEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id-123'),
			null,
		);

		$this->identityMap->add($entity);

		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id-123');
		\Tester\Assert::same($entity, $retrieved);
	}


	public function testGetReturnsNullForNonExistentEntity(): void
	{
		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'non-existent');
		\Tester\Assert::null($retrieved);
	}


	public function testAddSkipsEmptyElasticId(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$this->identityMap->add($entity);

		\Tester\Assert::true(empty($this->identityMap->identityMap));
	}


	public function testRemoveEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id-123'),
			null,
		);

		$this->identityMap->add($entity);
		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id-123'));

		$this->identityMap->remove($entity);
		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id-123'));
	}


	public function testMarkInsertedAddsEntityAndTracksState(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id-123'),
			null,
		);

		$this->identityMap->markInserted($entity);

		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id-123'));
		\Tester\Assert::true(isset($this->identityMap->persisted[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id-123']));
	}


	public function testIsChangedReturnsTrueForUnpersistedEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id-123'),
			null,
		);

		$this->identityMap->add($entity);

		\Tester\Assert::true($this->identityMap->isChanged($entity));
	}


	public function testIsChangedReturnsFalseForJustPersistedEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id-123'),
			null,
		);

		$this->identityMap->markInserted($entity);

		\Tester\Assert::false($this->identityMap->isChanged($entity));
	}


	public function testClearRemovesAllEntities(): void
	{
		$entity1 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('id-1'),
			null,
		);
		$entity2 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('id-2'),
			null,
		);

		$this->identityMap->add($entity1);
		$this->identityMap->add($entity2);

		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-1'));
		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-2'));

		$this->identityMap->clear();

		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-1'));
		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-2'));
	}


	public function testClearResetsAllInternalArrays(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);
		$this->identityMap->creatingEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id'] = true;
		$this->identityMap->uninitializedEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id'] = ['prop' => ['id' => \SpameriTests\Elastic\Data\Entity\Title::class]];

		// Verify arrays are populated
		\Tester\Assert::false(empty($this->identityMap->identityMap));
		\Tester\Assert::false(empty($this->identityMap->persisted));
		\Tester\Assert::false(empty($this->identityMap->creatingEntityList));
		\Tester\Assert::false(empty($this->identityMap->uninitializedEntityList));

		$this->identityMap->clear();

		// All arrays should be empty
		\Tester\Assert::true(empty($this->identityMap->identityMap));
		\Tester\Assert::true(empty($this->identityMap->persisted));
		\Tester\Assert::true(empty($this->identityMap->creatingEntityList));
		\Tester\Assert::true(empty($this->identityMap->uninitializedEntityList));
	}


	public function testClearOnEmptyIdentityMapDoesNotThrow(): void
	{
		// Should not throw any exceptions
		$this->identityMap->clear();

		\Tester\Assert::true(empty($this->identityMap->identityMap));
		\Tester\Assert::true(empty($this->identityMap->persisted));
	}


	public function testEntitiesCanBeAddedAfterClear(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->add($entity);
		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id'));

		$this->identityMap->clear();
		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id'));

		// Can add again
		$this->identityMap->add($entity);
		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id'));
	}


	public function testClearClearsPersistedStateTracking(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);
		\Tester\Assert::false($this->identityMap->isChanged($entity));

		$this->identityMap->clear();

		// After clear, entity should be seen as changed (not persisted)
		\Tester\Assert::true($this->identityMap->isChanged($entity));
	}


	public function testClearWithMultipleEntityTypes(): void
	{
		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-id'),
			null,
		);
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('image-id'),
			null,
		);

		$this->identityMap->add($title);
		$this->identityMap->add($image);

		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'title-id'));
		\Tester\Assert::notNull($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Image::class, 'image-id'));

		$this->identityMap->clear();

		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'title-id'));
		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Image::class, 'image-id'));
	}

}

(new IdentityMapTest())->run();
