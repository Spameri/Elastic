<?php declare(strict_types = 1);

namespace Spameri\Elastic\Settings;

abstract class AbstractIndexConfig implements IndexConfigInterface
{


	/**
	 * @param array<class-string> $entityClass
	 */
	public function __construct(
		protected string $index,
		protected array $entityClass,
	)
	{
	}


	public function entityClass(): array
	{
		return $this->entityClass;
	}

	public function indexName(): string
	{
		return $this->index;
	}

}
