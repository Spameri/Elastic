<?php declare(strict_types = 1);

namespace SpameriTests\Data\Model;


class PersonService extends \Spameri\Elastic\Model\BaseService
{

	/**
	 * @param \Spameri\Elastic\Entity\IElasticEntity|\SpameriTests\Data\Entity\Person $entity
	 * @return string
	 */
	public function insert(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : string
	{
		return parent::insert($entity);
	}


	/**
	 * @param \Spameri\Elastic\Entity\Property\ElasticId $id
	 * @return \Spameri\Elastic\Entity\IElasticEntity|\SpameriTests\Data\Entity\Person
	 */
	public function get(
		\Spameri\Elastic\Entity\Property\ElasticId $id
	) : \Spameri\Elastic\Entity\IElasticEntity
	{
		return parent::get($id);
	}


	/**
	 * @param \Spameri\ElasticQuery\ElasticQuery $elasticQuery
	 * @return \Spameri\Elastic\Entity\IElasticEntity|\SpameriTests\Data\Entity\Person
	 */
	public function getBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\IElasticEntity
	{
		return parent::getBy($elasticQuery);
	}


	/**
	 * @param \Spameri\ElasticQuery\ElasticQuery $elasticQuery
	 * @return \Spameri\Elastic\Entity\IElasticEntityCollection|array<\SpameriTests\Data\Entity\Person>
	 */
	public function getAllBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\IElasticEntityCollection
	{
		return parent::getAllBy($elasticQuery);
	}

}
