<?php

declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory;

require_once __DIR__ . '/../../../bootstrap.php';

class EntityFactory extends \SpameriTests\Elastic\AbstractTestCase
{


	public function testCreate()
	{
		$entityFactory = new \Spameri\Elastic\Factory\EntityFactory();

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'identification' => [
					'imdb' => 'nm0000001',
				],
				'name' => 'John Doe',
				'description' => 'John Doe is a great actor.',
				'birth' => '1970-01-01',
				'death' => NULL,
				'alias' => 'Johny',
				'characters' => [
					[
						'character' => 'Victor Doe',
						'video' => '1',
					],
				],
			],
			position: 0,
			index: '',
			type: '',
			id: 'asd123',
			score: 0.0,
			version: 0,
		);

		/** @var \SpameriTests\Elastic\Data\Entity\Person $entity */
		$entity = $entityFactory->create($hit, \SpameriTests\Elastic\Data\Entity\Person::class)->current();

		\var_dump($entity);

		\Tester\Assert::same('asd123', $entity->id->value());
		\Tester\Assert::same('nm0000001', $entity->identification()->imdb()->value());
		\Tester\Assert::same('John Doe', $entity->name()->value());
		\Tester\Assert::same('John Doe is a great actor.', $entity->description()->value());
		\Tester\Assert::same('1970-01-01', $entity->birth()->format('Y-m-d'));
		\Tester\Assert::null($entity->death());
		\Tester\Assert::same('Johny', $entity->alias()->value());
	}

}

(new EntityFactory())->run();
