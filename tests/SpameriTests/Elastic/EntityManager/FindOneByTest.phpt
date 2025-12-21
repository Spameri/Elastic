<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class FindOneByTest extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing indexes or aliases with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_TITLE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_IMAGE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_TITLE, []);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_IMAGE, []);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testFindOneByWithMatchingQuery(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(500000);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term('_id', $id),
		);

		$found = $entityManager->findOneBy($elasticQuery, \SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::notNull($found);
		\Tester\Assert::same($id, $found->id()->value());
	}


	public function testFindOneByWithNoMatchThrowsException(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term('_id', 'nonexistent-id-xyz'),
		);

		\Tester\Assert::exception(
			static fn () => $entityManager->findOneBy($elasticQuery, \SpameriTests\Elastic\Data\Entity\Title::class),
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);
	}


	public function testFindOneByWithMultipleMatchesReturnsFirst(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create multiple entities
		$entity1 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$entity2 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$entityManager->persist($entity1);
		$entityManager->persist($entity2);

		// Wait for ES to index
		\usleep(500000);

		// Query that matches all
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\MatchAll(),
		);

		$found = $entityManager->findOneBy($elasticQuery, \SpameriTests\Elastic\Data\Entity\Title::class);

		// Should return one entity
		\Tester\Assert::notNull($found);
		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\Title::class, $found);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_TITLE);
		} catch (\Throwable $e) {
			// Ignore
		}

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_IMAGE);
		} catch (\Throwable $e) {
			// Ignore
		}
	}

}

(new FindOneByTest())->run();
