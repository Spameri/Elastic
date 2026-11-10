<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Import;

/**
 * Mock after import handler for testing import system.
 */
class MockAfterImport implements \Spameri\Elastic\Import\AfterImportInterface
{

	/**
	 * @var array<array{entityData: array<mixed>, response: \Spameri\Elastic\Import\ResponseInterface}>
	 */
	private array $processedItems = [];


	public function process(array $entityData, \Spameri\Elastic\Import\ResponseInterface $result): void
	{
		$this->processedItems[] = [
			'entityData' => $entityData,
			'response' => $result,
		];
	}


	/**
	 * @return array<array{entityData: array<mixed>, response: \Spameri\Elastic\Import\ResponseInterface}>
	 */
	public function getProcessedItems(): array
	{
		return $this->processedItems;
	}


	public function getCallCount(): int
	{
		return \count($this->processedItems);
	}

}
