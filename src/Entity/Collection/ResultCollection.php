<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Collection;


class ResultCollection
{

	/**
	 * @var array
	 */
	private $metadata;


	public function __construct(
		array $metadata
	)
	{
		$this->metadata = $metadata;
	}


	public function count() : int
	{
		return $this->metadata['hits']['total'];
	}


	public function rows() : array
	{
		/** @var $data array */
		if (isset($this->metadata['hits']['hits'])) {
			$data = $this->metadata['hits']['hits'];

			$entities = [];
			foreach ($data as $hit) {
				$entityData = $hit['_source'];
				$entityData['id'] = $hit['_id'];
				$entities[] = $entityData;
			}

		} else {
			$entities = [$this->metadata];
		}

		return $entities;
	}
}
