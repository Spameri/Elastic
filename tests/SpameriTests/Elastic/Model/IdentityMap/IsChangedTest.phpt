<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\IdentityMap;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class IsChangedTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Model\IdentityMap $identityMap;


	protected function setUp(): void
	{
		$this->identityMap = new \Spameri\Elastic\Model\IdentityMap();
	}


	public function testIsChangedReturnsTrueForNewEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		// Entity is not yet marked as persisted
		\Tester\Assert::true($this->identityMap->isChanged($entity));
	}


	public function testIsChangedReturnsFalseAfterMarkInserted(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);

		// Entity should not be changed immediately after insert
		\Tester\Assert::false($this->identityMap->isChanged($entity));
	}


	public function testIsChangedReturnsTrueForEmptyIdEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		// Entity with empty ID is always considered changed (new)
		\Tester\Assert::true($this->identityMap->isChanged($entity));
	}


	public function testIsChangedReturnsTrueForDifferentEntitiesWithSameClass(): void
	{
		$entity1 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('id-1'),
			null,
		);
		$entity2 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('id-2'),
			null,
		);

		$this->identityMap->markInserted($entity1);

		// Entity1 should not be changed
		\Tester\Assert::false($this->identityMap->isChanged($entity1));
		// Entity2 should be changed (not persisted yet)
		\Tester\Assert::true($this->identityMap->isChanged($entity2));
	}


	public function testIsChangedReturnsTrueForDifferentEntityClasses(): void
	{
		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('same-id'),
			null,
		);
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('same-id'),
			null,
		);

		$this->identityMap->markInserted($title);

		// Title should not be changed
		\Tester\Assert::false($this->identityMap->isChanged($title));
		// Image should be changed (different class, not persisted)
		\Tester\Assert::true($this->identityMap->isChanged($image));
	}


	public function testMultipleMarksDoNotAffectIsChanged(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);
		$this->identityMap->markInserted($entity);
		$this->identityMap->markInserted($entity);

		\Tester\Assert::false($this->identityMap->isChanged($entity));
	}

}

(new IsChangedTest())->run();
