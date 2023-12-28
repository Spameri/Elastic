<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Collection;

abstract class AbstractCachedEntityCollection extends AbstractEntityCollection
{

	protected bool $initialized = FALSE;


	abstract public function initialize(): void;

	public function entity(
		string $key,
	): \Spameri\Elastic\Entity\EntityInterface|null
	{
		$this->initialize();

		return parent::entity($key);
	}


	public function remove(
		string $key,
	): void
	{
		$this->initialize();

		parent::remove($key);
	}


	public function isValue(
		string $key,
	): bool
	{
		$this->initialize();

		return parent::isValue($key);
	}


	public function count(): int
	{
		$this->initialize();

		return parent::count();
	}


	public function keys(): array
	{
		$this->initialize();

		return parent::keys();
	}


	public function isKey(
		string $key,
	): bool
	{
		$this->initialize();

		return parent::isKey($key);
	}


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField, // phpcs:ignore
		string $type,
	): void
	{
		$this->initialize();

		parent::sort($sortField, $type);
	}

}
