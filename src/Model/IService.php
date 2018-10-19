<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


interface IService
{

	public function insert(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : string;


	public function get(
		\Spameri\Elastic\Entity\Property\ElasticId $id
	) : \Spameri\Elastic\Entity\IElasticEntity;


	public function getBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\IElasticEntity;


	public function getAllBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\IElasticEntityCollection;


	public function delete(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : bool;

}
