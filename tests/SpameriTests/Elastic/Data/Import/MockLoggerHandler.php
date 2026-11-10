<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Import;

/**
 * Mock logger handler for testing import system.
 */
class MockLoggerHandler implements \Spameri\Elastic\Import\LoggerHandlerInterface
{

	/**
	 * @var array<mixed>
	 */
	private array $itemsStarted = [];

	/**
	 * @var array<\Spameri\Elastic\Entity\AbstractImport>
	 */
	private array $preparedEntities = [];

	/**
	 * @var array<\Spameri\Elastic\Import\ResponseInterface>
	 */
	private array $responses = [];

	/**
	 * @var array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	private array $omitExceptions = [];

	/**
	 * @var array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	private array $errorExceptions = [];

	/**
	 * @var array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	private array $fatalExceptions = [];

	private bool $finishCalled = false;


	public function logItemStart(mixed $item): void
	{
		$this->itemsStarted[] = $item;
	}


	public function logPrepared(\Spameri\Elastic\Entity\AbstractImport $import): void
	{
		$this->preparedEntities[] = $import;
	}


	public function logResponse(\Spameri\Elastic\Import\ResponseInterface $result): void
	{
		$this->responses[] = $result;
	}


	public function logOmitException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->omitExceptions[] = $exception;
	}


	public function logErrorException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->errorExceptions[] = $exception;
	}


	public function logFatalException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->fatalExceptions[] = $exception;
	}


	public function logFinish(): void
	{
		$this->finishCalled = true;
	}


	/**
	 * @return array<mixed>
	 */
	public function getItemsStarted(): array
	{
		return $this->itemsStarted;
	}


	/**
	 * @return array<\Spameri\Elastic\Entity\AbstractImport>
	 */
	public function getPreparedEntities(): array
	{
		return $this->preparedEntities;
	}


	/**
	 * @return array<\Spameri\Elastic\Import\ResponseInterface>
	 */
	public function getResponses(): array
	{
		return $this->responses;
	}


	/**
	 * @return array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	public function getOmitExceptions(): array
	{
		return $this->omitExceptions;
	}


	/**
	 * @return array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	public function getErrorExceptions(): array
	{
		return $this->errorExceptions;
	}


	/**
	 * @return array<\Spameri\Elastic\Import\Exception\ImportException>
	 */
	public function getFatalExceptions(): array
	{
		return $this->fatalExceptions;
	}


	public function isFinishCalled(): bool
	{
		return $this->finishCalled;
	}

}
