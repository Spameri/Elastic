<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Search
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $index
	) : ?array
	{
		$result = $this->clientProvider->client()->search(
			(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain($elasticQuery->toArray()),
					$index
				)
			)
				->toArray()
		);

		return $result;
	}

}
