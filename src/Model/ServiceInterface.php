<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


interface ServiceInterface
{

	public function insert(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity
	): string;


	public function get(
		\Spameri\Elastic\Entity\Property\ElasticId $id
	): \Spameri\Elastic\Entity\ElasticEntityInterface;


	public function getBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	): \Spameri\Elastic\Entity\ElasticEntityInterface;


	public function getAllBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface;


	public function delete(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id
	): bool;


	public function aggregate(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	): \Spameri\ElasticQuery\Response\ResultSearch;

}
