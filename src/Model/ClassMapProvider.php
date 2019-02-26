<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class ClassMapProvider
{

	public function provide(
		string $entityClass
	) : array
	{
		return $this->classMap[$entityClass];
	}

}
