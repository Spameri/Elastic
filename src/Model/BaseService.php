<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


abstract class BaseService implements IService
{
	public const ELASTIC_INDEX = 'spameri';

	/**
	 * @var string
	 */
	protected $index;

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


	public function __construct(
		string $index
		, array $entityProperties
		, \Spameri\Elastic\ClientProvider $client
		, Insert $insert
		, Get $get
		, GetBy $getBy
		, GetAllBy $getAllBy
		, Delete $delete
	) {
		$this->client = $client->client();
		$this->index = $index;
		$this->insert = $insert;
		$this->get = $get;
		$this->delete = $delete;
		$this->getBy = $getBy;
		$this->getAllBy = $getAllBy;
		$this->entityProperties = $entityProperties;
	}

}
