<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


interface IEntityFactory
{

	public function create(
		\Spameri\Elastic\Entity\Collection\ResultCollection $collection
	) : \Generator;

}
