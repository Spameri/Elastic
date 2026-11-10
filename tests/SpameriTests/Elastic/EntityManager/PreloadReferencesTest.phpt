<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Single-entity references of a whole page load in one query instead of one find() per hit.
 *
 * @testCase
 */
class PreloadReferencesTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private \Spameri\Elastic\EntityManager $entityManager;


	protected function setUp(): void
	{
		parent::setUp();

		$this->deleteIndices();

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_TITLE, []);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_IMAGE, []);

		$this->entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
	}


	public function testPageOfReferencesLoadsInOneQuery(): void
	{
		$imageIds = [];

		for ($i = 0; $i < 5; $i++) {
			$image = new \SpameriTests\Elastic\Data\Entity\Image(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$this->entityManager->persist($image);

			$title = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				$image,
			);
			$this->entityManager->persist($title);

			$imageIds[$title->id()->value()] = $image->id()->value();
		}

		$this->refreshIndices();
		$this->entityManager->clear();

		$searchesBefore = $this->imageSearchCount();
		$titles = $this->entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class);
		$searches = $this->imageSearchCount() - $searchesBefore;

		\Tester\Assert::same(5, $titles->count());
		\Tester\Assert::same(1, $searches);

		/** @var \SpameriTests\Elastic\Data\Entity\Title $title */
		foreach ($titles as $title) {
			\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\Image::class, $title->backdrop);
			\Tester\Assert::same($imageIds[$title->id()->value()], $title->backdrop->id()->value());
		}
	}


	public function testSharedReferenceResolvesToOneInstance(): void
	{
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$this->entityManager->persist($image);

		for ($i = 0; $i < 3; $i++) {
			$this->entityManager->persist(
				new \SpameriTests\Elastic\Data\Entity\Title(
					new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
					$image,
				),
			);
		}

		$this->refreshIndices();
		$this->entityManager->clear();

		$titles = \array_values(\iterator_to_array($this->entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class)));

		\Tester\Assert::count(3, $titles);
		\Tester\Assert::same($titles[0]->backdrop, $titles[1]->backdrop);
		\Tester\Assert::same($titles[0]->backdrop, $titles[2]->backdrop);
	}


	public function testReferenceCycleHydratesBothSides(): void
	{
		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$title,
		);
		$this->entityManager->persist($image);
		$title->backdrop = $image;
		$this->entityManager->persist($title);

		$this->refreshIndices();
		$this->entityManager->clear();

		$titles = $this->entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::same(1, $titles->count());
		/** @var \SpameriTests\Elastic\Data\Entity\Title $loadedTitle */
		$loadedTitle = $titles->first();
		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\Image::class, $loadedTitle->backdrop);
		\Tester\Assert::same($image->id()->value(), $loadedTitle->backdrop->id()->value());
		\Tester\Assert::same($loadedTitle, $loadedTitle->backdrop->title);
	}


	public function testAlreadyLoadedReferenceIsNotQueriedAgain(): void
	{
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$this->entityManager->persist($image);
		$this->entityManager->persist(
			new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				$image,
			),
		);

		$this->refreshIndices();
		$this->entityManager->clear();

		$loadedImage = $this->entityManager->find($image->id()->value(), \SpameriTests\Elastic\Data\Entity\Image::class);

		$searchesBefore = $this->imageSearchCount();
		$titles = $this->entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::same(0, $this->imageSearchCount() - $searchesBefore);
		\Tester\Assert::same($loadedImage, $titles->first()->backdrop);
	}


	public function testMissingReferenceStillDropsTheReferencingHit(): void
	{
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$this->entityManager->persist($image);
		$this->entityManager->persist(
			new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				$image,
			),
		);
		$this->entityManager->persist(
			new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			),
		);

		$this->refreshIndices();
		$this->entityManager->clear();

		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		$clientProvider->client()->delete([
			'index' => \SpameriTests\Elastic\Config::INDEX_IMAGE,
			'id' => $image->id()->value(),
			'refresh' => true,
		]);

		$titles = $this->entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::same(1, $titles->count());
		\Tester\Assert::null($titles->first()->backdrop);
	}


	protected function tearDown(): void
	{
		$this->deleteIndices();
	}


	private function deleteIndices(): void
	{
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);

		foreach ([\SpameriTests\Elastic\Config::INDEX_TITLE, \SpameriTests\Elastic\Config::INDEX_IMAGE] as $index) {
			try {
				$clientProvider->client()->indices()->delete(['index' => $index]);
			} catch (\Throwable) {
				// The index does not exist yet.
			}
		}
	}


	private function refreshIndices(): void
	{
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		$clientProvider->client()->indices()->refresh([
			'index' => \SpameriTests\Elastic\Config::INDEX_TITLE . ',' . \SpameriTests\Elastic\Config::INDEX_IMAGE,
		]);
	}


	private function imageSearchCount(): int
	{
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		$stats = $clientProvider->client()->indices()->stats([
			'index' => \SpameriTests\Elastic\Config::INDEX_IMAGE,
			'metric' => 'search',
		])->asArray();

		return (int) $stats['_all']['primaries']['search']['query_total'];
	}

}

(new PreloadReferencesTest())->run();
