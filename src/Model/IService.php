<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


interface IService
{

	public function insert(\Spameri\Elastic\Entity\IElasticEntity $entity) : string;


	public function get(\Spameri\Elastic\Entity\Property\ElasticId $id) : \Spameri\Elastic\Entity\IElasticEntity;


	public function getBy(array $options) : \Spameri\Elastic\Entity\IElasticEntity;


	public function getAllBy(array $options) : \Spameri\Elastic\Entity\IEntityCollection;


	public function delete(\Spameri\Elastic\Entity\Property\IElasticId $id) : bool;

}
