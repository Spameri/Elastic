<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


abstract class BaseService implements IService
{
	const ELASTIC_INDEX = 'spameri';

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var \Kdyby\ElasticSearch\Client
	 */
	protected $client;

	/**
	 * @var \Spameri\Elastic\Model\Insert
	 */
	protected $insert;

	/**
	 * @var \Spameri\Elastic\Model\Get
	 */
	protected $get;

	/**
	 * @var \Spameri\Elastic\Model\Delete
	 */
	protected $delete;

	/**
	 * @var \Spameri\Elastic\Model\GetBy
	 */
	protected $getBy;

	/**
	 * @var \Spameri\Elastic\Model\GetAllBy
	 */
	protected $getAllBy;

	/**
	 * @var array
	 */
	protected $entityProperties;

	/**
	 * @var \Elastica\Type
	 */
	protected $elasticType;


	public function __construct(
		string $type
		, array $entityProperties
		, \Kdyby\ElasticSearch\Client $client
		, Insert $insert
		, Get $get
		, GetBy $getBy
		, GetAllBy $getAllBy
		, Delete $delete
	) {
		$this->client = $client;
		$this->type = $type;
		$this->elasticType = $this->client->getIndex(BaseService::ELASTIC_INDEX)->getType($type);
		$this->insert = $insert;
		$this->get = $get;
		$this->delete = $delete;
		$this->getBy = $getBy;
		$this->getAllBy = $getAllBy;
		$this->entityProperties = $entityProperties;
	}

}
