<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Import;

/**
 * Mock data provider for testing import system.
 */
class MockDataProvider implements \Spameri\Elastic\Import\DataProviderInterface
{

	/**
	 * @var array<mixed>
	 */
	private array $data = [];

	/**
	 * @param array<mixed> $data
	 */
	public function setData(array $data): void
	{
		$this->data = $data;
	}


	public function provide(\Spameri\Elastic\Import\Run\Options $options): \Generator // phpcs:ignore
	{
		foreach ($this->data as $index => $item) { // phpcs:ignore
			yield $item;
		}
	}


	public function count(\Spameri\Elastic\Import\Run\Options $options): int // phpcs:ignore
	{
		return \count($this->data);
	}

}
