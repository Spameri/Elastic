<?php declare(strict_types = 1);

namespace Spameri\Elastic\Diagnostics;

readonly class Panel implements \Tracy\IBarPanel
{

	public function __construct(
		private \Spameri\Elastic\Diagnostics\PanelLogger $logger,
	)
	{
	}


	public function getTab(): string
	{
		// phpcs:disable
		$queries = $this->getQueries();
		$queriesDuration = $this->getQueriesDuration();
		// phpcs:enable
		\ob_start();
		require __DIR__ . '/Panel/tab.phtml';

		return (string) \ob_get_clean();
	}


	public function getPanel(): string
	{
		// phpcs:disable
		$queries = $this->getQueries();
		$queriesHeader = $this->getQueriesHeader();
		$queriesDuration = $this->getQueriesDuration();
		$requestBodies = $this->logger->getRequestBodies();
		$responseBodies = $this->logger->getResponseBodies();
		// phpcs:enable
		\ob_start();
		require __DIR__ . '/Panel/panel.phtml';

		return (string) \ob_get_clean();
	}


	/**
	 * @return array<mixed>
	 */
	private function getQueries(): array
	{
		return $this->logger->getQueries();
	}


	/**
	 * @return array<string>
	 */
	private function getQueriesHeader(): array
	{
		return [
			'uri',
			'duration',
		];
	}


	private function getQueriesDuration(): float
	{
		return \array_sum(\array_column($this->getQueries(), 'duration'));
	}

}
