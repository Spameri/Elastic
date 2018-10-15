<?php declare(strict_types = 1);

namespace Spameri\Elastic\Diagnostics;


class Panel implements \Tracy\IBarPanel
{

	/**
	 * @var \Spameri\Elastic\Diagnostics\PanelLogger
	 */
	private $logger;


	public function __construct(
		\Spameri\Elastic\Diagnostics\PanelLogger $logger
	)
	{
		$this->logger = $logger;
	}


	public function getTab()
	{
		$queries = $this->getQueries();
		$queriesDuration = $this->getQueriesDuration();
		ob_start();
		require __DIR__ . '/Panel/tab.phtml';

		return ob_get_clean();
	}


	public function getPanel()
	{
		$queries = $this->getQueries();
		$queriesHeader = $this->getQueriesHeader();
		$queriesDuration = $this->getQueriesDuration();
		$requestBodies = $this->logger->getRequestBodies();
		$responseBodies = $this->logger->getResponseBodies();
		ob_start();
		require __DIR__ . '/Panel/panel.phtml';

		return ob_get_clean();
	}


	private function getQueries(): array
	{
		return $this->logger->getQueries();
	}


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
