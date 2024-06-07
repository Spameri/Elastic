<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;

class ImdbIdCollection implements \Spameri\Elastic\Entity\ValueCollectionInterface
{

	/**
	 * @var array<\SpameriTests\Elastic\Data\Entity\Property\ImdbId>
	 */
	private array $collection;


	public function __construct(
		ImdbId ...$collection,
	)
	{
		$this->collection = [];
		foreach ($collection as $imdbId) {
			$this->add($imdbId);
		}
	}


	public function add(
		ImdbId $imdbId,
	): void
	{
		$this->collection[$imdbId->value()] = $imdbId;
	}


	public function find(
		ImdbId $imdbId,
	): ImdbId|null
	{
		foreach ($this->collection as $value) {
			if ($imdbId->value() === $value->value()) {
				return $value;
			}
		}

		return null;
	}


	public function keys(): array
	{
		return \array_keys($this->collection);
	}


	public function first(): ImdbId|null
	{
		$first = \reset($this->collection);

		if ($first === false) {
			throw new \Nette\InvalidStateException();
		}

		return $first;
	}


	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}

}
