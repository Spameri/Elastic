<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


interface IAggregation
{

	public function toArray() : array;
}
