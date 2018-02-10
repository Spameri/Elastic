<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface IEntity
{

	public function key() : string;


	public function entityVariables() : array;

}
