<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Import;

/**
 * Mock data import for testing import system.
 */
class MockDataImport implements \Spameri\Elastic\Import\DataImportInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\AbstractImport>
	 */
	private array $importedEntities = [];

	/**
	 * @var array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	private array $exceptionsToThrow = [];

	private int $callCount = 0;

	private bool $shouldFail = false;


	public function setExceptionForCall(int $callIndex, \Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->exceptionsToThrow[$callIndex] = $exception;
	}


	public function setShouldFail(bool $shouldFail): void
	{
		$this->shouldFail = $shouldFail;
	}


	public function import(\Spameri\Elastic\Entity\AbstractImport $entity): \Spameri\Elastic\Import\ResponseInterface
	{
		$currentCall = $this->callCount++;

		if (isset($this->exceptionsToThrow[$currentCall])) {
			throw $this->exceptionsToThrow[$currentCall];
		}

		$this->importedEntities[] = $entity;

		$response = $this->shouldFail ? null : ['result' => 'created', '_id' => (string) $entity->key()];

		return new \Spameri\Elastic\Import\Response\SimpleResponse($response, $entity);
	}


	/**
	 * @return array<\Spameri\Elastic\Entity\AbstractImport>
	 */
	public function getImportedEntities(): array
	{
		return $this->importedEntities;
	}


	public function getCallCount(): int
	{
		return $this->callCount;
	}

}
