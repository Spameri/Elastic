<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface ElasticEntityInterface
{

	public function id(): \Spameri\Elastic\Entity\Property\ElasticIdInterface;


	public function entityVariables(): array;

}
