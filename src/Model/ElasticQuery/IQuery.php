<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


interface IQuery
{
	public function toArray() : array;
}
