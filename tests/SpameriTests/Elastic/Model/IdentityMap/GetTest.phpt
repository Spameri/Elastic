<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\IdentityMap;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class GetTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Model\IdentityMap $identityMap;


	protected function setUp(): void
	{
		$this->identityMap = new \Spameri\Elastic\Model\IdentityMap();
	}


	public function testGetReturnsStoredEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->add($entity);

		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id');
		\Tester\Assert::same($entity, $retrieved);
	}


	public function testGetReturnsNullForMissingEntity(): void
	{
		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'nonexistent');
		\Tester\Assert::null($retrieved);
	}


	public function testGetReturnsNullForWrongClass(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->add($entity);

		// Try to get with wrong class
		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Image::class, 'test-id');
		\Tester\Assert::null($retrieved);
	}


	public function testGetWithIntegerId(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('123'),
			null,
		);

		$this->identityMap->add($entity);

		// Can retrieve with int (will be cast to string key)
		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 123);
		\Tester\Assert::same($entity, $retrieved);
	}


	public function testGetMultipleEntitiesSameClass(): void
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

		\Tester\Assert::same($entity1, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-1'));
		\Tester\Assert::same($entity2, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-2'));
	}

}

(new GetTest())->run();
