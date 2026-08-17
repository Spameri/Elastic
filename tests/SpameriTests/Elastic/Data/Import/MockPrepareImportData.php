<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Import;

/**
 * Mock prepare import data for testing import system.
 */
class MockPrepareImportData implements \Spameri\Elastic\Import\PrepareImportDataInterface
{

	/**
	 * @var array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	private array $exceptionsToThrow = [];

	private int $callCount = 0;


	public function setExceptionForCall(int $callIndex, \Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->exceptionsToThrow[$callIndex] = $exception;
	}


	public function prepare(mixed $entityData): \Spameri\Elastic\Entity\AbstractImport
	{
		$currentCall = $this->callCount++;

		if (isset($this->exceptionsToThrow[$currentCall])) {
			throw $this->exceptionsToThrow[$currentCall];
		}

		// Create a simple import entity from the data
		return new \SpameriTests\Elastic\Data\Entity\TestImportEntity(
			$entityData['key'] ?? $currentCall,
			$entityData['name'] ?? 'Test',
			$entityData['value'] ?? 0,
		);
	}


	public function getCallCount(): int
	{
		return $this->callCount;
	}

}
