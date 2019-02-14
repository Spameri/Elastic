<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


interface IEntityFactory
{

	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit
	) : \Generator;

}
