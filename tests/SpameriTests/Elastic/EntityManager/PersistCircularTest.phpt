<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Persisting an entity whose nested elastic entities reference it back must not
 * loop: the parent is indexed first (so it has an id), then the children are
 * persisted referencing the parent, then the parent is re-indexed.
 *
 * @testCase
 */
class PersistCircularTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testPersistCircularReference(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$title,
			true,
		);
		// circular reference: title -> image -> title
		$title->backdrop = $image;

		// must finish (no infinite loop) and assign both ids
		\Tester\Assert::noError(
			static function () use ($entityManager, $title): void {
				$entityManager->persist($title);
			},
		);

		\Tester\Assert::false($title->id() instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId);
		\Tester\Assert::false($image->id() instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId);
		\Tester\Assert::notSame('', $title->id()->value());
		\Tester\Assert::notSame('', $image->id()->value());

		// each side recorded the other's resolved id on the in-memory graph
		\Tester\Assert::same($image->id()->value(), $title->backdrop->id()->value());
		\Tester\Assert::same($title->id()->value(), $image->title->id()->value());

		// read the stored documents and verify the references were persisted
		$client = $this->container->getByType(\Spameri\Elastic\ClientProvider::class)->client();

		$titleSource = $client->get([
			'index' => \SpameriTests\Elastic\Config::INDEX_TITLE,
			'id' => $title->id()->value(),
		])->asArray()['_source'];
		$imageSource = $client->get([
			'index' => \SpameriTests\Elastic\Config::INDEX_IMAGE,
			'id' => $image->id()->value(),
		])->asArray()['_source'];

		\Tester\Assert::same($image->id()->value(), $titleSource['backdrop']);
		\Tester\Assert::same($title->id()->value(), $imageSource['title']);
	}

}

(new PersistCircularTest())->run();
