<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


abstract class AbstractEntity implements IEntity
{

	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function key() : string
	{
		return \md5(\implode('_', $this->entityVariables()));
	}

}
