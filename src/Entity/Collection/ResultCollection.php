<?php declare(strict_types = 1);

namespace Spameri\Model\Collection;


class ResultCollection extends Collection
{

	public function getCount() : int
	{
		return $this->metadata['hits']['total'];
	}


	public function setCount(int $count)
	{
		$this->metadata['hits']['total'] = $count;
	}


	public function getRows() : \Generator
	{
		/**
		 * @var $data array
		 */
		$data = $this->metadata['hits']['hits'];

		foreach ($data as $hit) {
			$entityData = $hit['_source'];
			$entityData['id'] = $hit['_id'];
			yield new $this->entity($entityData);
		}
	}


	public function setRows(array $rows)
	{
		$this->metadata['hits']['hits'] = $rows;
	}
}
