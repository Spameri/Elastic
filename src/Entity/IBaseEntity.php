<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface IBaseEntity
{

	public function metadata() : array;


	public function toArray() : array;

}
