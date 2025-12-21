<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Simple entity implementing EntityInterface for testing nested event dispatch.
 */
class SimpleNestedEntity implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private string $key,
		private string $name,
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function entityVariables(): array
	{
		return [
			'key' => $this->key,
			'name' => $this->name,
		];
	}


	public function getName(): string
	{
		return $this->name;
	}

}
