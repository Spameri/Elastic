<?php

declare(strict_types = 1);

namespace Spameri\Elastic\Settings;

abstract class AbstractIndexConfig implements IndexConfigInterface
{


	public function __construct(
		protected string $index,
		protected string $entityClass,
	)
	{
	}


	public function entityClass(): string
	{
		return $this->entityClass;
	}

	public function indexName(): string
	{
		return $this->index;
	}

}
