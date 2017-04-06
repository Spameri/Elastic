<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


interface IFilter
{
	public function toArray() : array;
}
