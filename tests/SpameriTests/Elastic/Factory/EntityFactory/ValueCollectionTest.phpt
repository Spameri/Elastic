<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory\EntityFactory;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * A value collection is written as a flat list of scalars and has to come back
 * as the same list of value objects.
 *
 * PrepareEntityArray has always known how: it walks a ValueCollectionInterface
 * and writes value() for each member. EntityFactory did not, so the property
 * fell through to the generic "instantiate from nested properties" tail, where
 * the collection's own properties are looked for under `genres.*` — nothing is
 * stored there, because the document holds `genres: ["Action"]` — and every
 * read produced an empty collection.
 *
 * Nothing reported an error. The data was in Elasticsearch, indexed and
 * searchable, and simply never reached anything that read an entity.
 *
 * @testCase
 */
class ValueCollectionTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testValueCollectionSurvivesTheRoundTrip(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray */
		$prepareEntityArray = $this->container->getByType(\Spameri\Elastic\Model\Insert\PrepareEntityArray::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\EntityWithValueCollection(
			new \Spameri\Elastic\Entity\Property\ElasticId('vc-1'),
			new \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection(
				new \SpameriTests\Elastic\Data\Entity\Video\Details\Genre('Action'),
				new \SpameriTests\Elastic\Data\Entity\Video\Details\Genre('Science Fiction'),
			),
		);

		$source = $prepareEntityArray->prepare($entity);

		// What the write side puts in the document: a flat list of scalars.
		\Tester\Assert::same(['Action', 'Science Fiction'], $source['genres']);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: $source,
			position: 0, index: '', type: '', id: 'vc-1', score: 0.0, version: 0,
		);

		/** @var \SpameriTests\Elastic\Data\Entity\EntityWithValueCollection $hydrated */
		$hydrated = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueCollection::class,
			$entityManager,
		);

		$genres = [];
		foreach ($hydrated->genres as $genre) {
			$genres[] = $genre->value();
		}

		\Tester\Assert::same(['Action', 'Science Fiction'], $genres);
	}


	public function testAnEmptyValueCollectionStaysEmpty(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['genres' => []],
			position: 0, index: '', type: '', id: 'vc-2', score: 0.0, version: 0,
		);

		/** @var \SpameriTests\Elastic\Data\Entity\EntityWithValueCollection $hydrated */
		$hydrated = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueCollection::class,
			$entityManager,
		);

		\Tester\Assert::same(0, \iterator_count($hydrated->genres->getIterator()));
	}


	public function testAnAbsentValueCollectionIsNotAnError(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		// A document written before the field existed. It has to read as empty
		// rather than throw, or one old document takes down a whole index.
		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [],
			position: 0, index: '', type: '', id: 'vc-3', score: 0.0, version: 0,
		);

		/** @var \SpameriTests\Elastic\Data\Entity\EntityWithValueCollection $hydrated */
		$hydrated = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueCollection::class,
			$entityManager,
		);

		\Tester\Assert::same(0, \iterator_count($hydrated->genres->getIterator()));
	}


	public function testNullMembersAreNotTurnedIntoValues(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['genres' => ['Action', NULL, '', 'Drama']],
			position: 0, index: '', type: '', id: 'vc-4', score: 0.0, version: 0,
		);

		/** @var \SpameriTests\Elastic\Data\Entity\EntityWithValueCollection $hydrated */
		$hydrated = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueCollection::class,
			$entityManager,
		);

		$genres = [];
		foreach ($hydrated->genres as $genre) {
			$genres[] = $genre->value();
		}

		\Tester\Assert::same(['Action', 'Drama'], $genres);
	}

}

(new ValueCollectionTest())->run();
