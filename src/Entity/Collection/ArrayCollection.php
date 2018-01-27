<?php declare(strict_types = 1);

namespace Spameri\Model\Collection;


class ArrayCollection extends Collection
{

	public function getCount() : int
	{
		return count($this->metadata);
	}


	public function setCount(int $count)
	{
		throw new \Nette\NotImplementedException();
	}


	public function getRows() : \Generator
	{
		foreach ($this->metadata as $hit) {
			yield new $this->entity($hit);
		}
	}


	public function setRows(array $rows)
	{
		throw new \Nette\NotImplementedException();
	}
}
