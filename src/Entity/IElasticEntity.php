<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface IElasticEntity extends IEntity
{

	public function id() : \Spameri\Elastic\Entity\Property\IElasticId;
}
