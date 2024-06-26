<?php declare(strict_types = 1);

namespace Spameri\Elastic;

class ClientProvider
{

	private \Elastic\Elasticsearch\Client $client;


	public function __construct(
		private readonly \Elastic\Elasticsearch\ClientBuilder $clientBuilder,
		private readonly \Spameri\Elastic\SettingsProviderInterface $settingsProvider,
	)
	{
		$this->init();
	}


	public function init(): void
	{
		$settings = $this->settingsProvider->provide();
		$this->clientBuilder->setHosts(
			[
				$settings->host() . ':' . $settings->port(),
			],
		);
	}


	/**
	 * @throws \Elastic\Elasticsearch\Exception\ElasticsearchException
	 */
	public function client(): \Elastic\Elasticsearch\Client
	{
		if ( ! ($this->client instanceof \Elastic\Elasticsearch\Client)) {
			$this->client = $this->clientBuilder->build();
		}

		return $this->client;
	}

}
