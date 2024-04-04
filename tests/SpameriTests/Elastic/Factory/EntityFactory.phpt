<?php

declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory;

require_once __DIR__ . '/../../../bootstrap.php';

class EntityFactory extends \SpameriTests\Elastic\AbstractTestCase
{


	public function testCreate()
	{
		$entityFactory = new \Spameri\Elastic\Factory\EntityFactory();
		$prepareEntityArray = new \Spameri\Elastic\Model\Insert\PrepareEntityArray(
			\Mockery::mock(\Spameri\Elastic\Model\ServiceLocator::class)
		);
		$person = new \SpameriTests\Elastic\Data\Entity\Person(
			new \Spameri\Elastic\Entity\Property\ElasticId('asd123'),
			new \SpameriTests\Elastic\Data\Entity\Video\Identification(
				new \SpameriTests\Elastic\Data\Entity\Property\ImdbId('nm0000001')
			),
			new \SpameriTests\Elastic\Data\Entity\Property\Name('John Doe'),
			new \SpameriTests\Elastic\Data\Entity\Property\Description('John Doe is a great actor.'),
			new \Spameri\Elastic\Entity\Property\Date('1970-01-01'),
			NULL,
			new \SpameriTests\Elastic\Data\Entity\Property\Name('Johny'),
			new \Spameri\Elastic\Entity\Collection\EntityCollection(
				new \SpameriTests\Elastic\Data\Entity\Person\Character(
					'ch0000001',
					new \SpameriTests\Elastic\Data\Entity\Property\ImdbId('ep0000001'),
					new \SpameriTests\Elastic\Data\Entity\Property\Name('John Doe'),
					new \SpameriTests\Elastic\Data\Entity\Property\Name('Johny'),
					new \SpameriTests\Elastic\Data\Entity\Video\Identification(
						new \SpameriTests\Elastic\Data\Entity\Property\ImdbId('nm0000002')
					),
					new \SpameriTests\Elastic\Data\Entity\Property\Description('John Doe is a great actor.')
				)
			),
			new \Spameri\Elastic\Entity\Collection\EntityCollection(),
		);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: $prepareEntityArray->prepare($person),
			position: 0,
			index: '',
			type: '',
			id: 'asd123',
			score: 0.0,
			version: 0,
		);

		/** @var \SpameriTests\Elastic\Data\Entity\Person $entity */
		$entity = $entityFactory->create($hit, \SpameriTests\Elastic\Data\Entity\Person::class)->current();

		\Tester\Assert::same($person->id->value(), $entity->id->value());
		\Tester\Assert::same($person->identification->imdb->value(), $entity->identification->imdb->value());
		\Tester\Assert::same($person->name->value(), $entity->name->value());
		\Tester\Assert::same($person->description->value(), $entity->description->value());
		\Tester\Assert::same($person->birth->format('Y-m-d'), $entity->birth->format('Y-m-d'));
		\Tester\Assert::null($entity->death);
		\Tester\Assert::same($person->alias->value(), $entity->alias->value());
	}

}

(new EntityFactory())->run();
