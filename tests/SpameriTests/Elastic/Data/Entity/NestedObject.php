<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Simple nested object (not an ElasticEntity) for testing nested hydration.
 */
class NestedObject implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		public string $title,
		public int $order,
	)
	{
	}


	public function key(): string
	{
		return (string) $this->order;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function entityVariables(): array
	{
		return [
			'title' => $this->title,
			'order' => $this->order,
		];
	}

}
