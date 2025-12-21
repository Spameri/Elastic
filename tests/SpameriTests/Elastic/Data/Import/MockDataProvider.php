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
	 * @var array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	private array $exceptionsToThrow = [];


	/**
	 * @param array<mixed> $data
	 */
	public function setData(array $data): void
	{
		$this->data = $data;
	}


	public function setExceptionForItem(int $index, \Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->exceptionsToThrow[$index] = $exception;
	}


	public function provide(\Spameri\Elastic\Import\Run\Options $options): \Generator
	{
		foreach ($this->data as $index => $item) {
			yield $item;
		}
	}


	public function count(\Spameri\Elastic\Import\Run\Options $options): int
	{
		return \count($this->data);
	}

}
