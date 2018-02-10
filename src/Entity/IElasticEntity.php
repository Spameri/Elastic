<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface IElasticEntity
{

	public function id() : \Spameri\Elastic\Entity\Property\IElasticId;


	public function entityVariables() : array;

}
