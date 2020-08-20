<?php declare(strict_types = 1);

namespace Spameri\Elastic\Exception;


class DocumentNotFound extends \Spameri\Elastic\Exception\AbstractElasticSearchException
{

	public function __construct(
		string $message
		, ?\Spameri\ElasticQuery\ElasticQuery $elasticQuery = NULL
	)
	{
		$queryString = '';
		if ($elasticQuery) {
			try {
				$queryString = \Nette\Utils\Json::encode($elasticQuery->toArray());

			} catch (\Nette\Utils\JsonException $exception) {
				$queryString = 'not valid json';
			}
		}

		parent::__construct(
			'Document in index "' . $message . '" not found. With query: '
			. $queryString
		);
	}

}
