<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Property;


interface IElasticId
{

	public function value() : string;

}
