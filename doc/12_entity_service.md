# Entity service

## Description
TODO
Every service should extend BaseService which has all methods for entity manipulation. Like **Insert**, **Get**, 
**GetBy**, **GetAllBy**.

## Example
```php
namespace SpameriTests\Elastic\Data\Model;


class VideoService extends \Spameri\Elastic\Model\AbstractBaseService
{

	/**
	 * @param \Spameri\Elastic\Entity\ElasticEntityInterface|\SpameriTests\Elastic\Data\Entity\Video $entity
	 * @return string
	 */
	public function insert(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity
	) : string
	{
		return parent::insert($entity);
	}


	/**
	 * @param \Spameri\Elastic\Entity\Property\ElasticId $id
	 * @return \Spameri\Elastic\Entity\ElasticEntityInterface|\SpameriTests\Elastic\Data\Entity\Video
	 */
	public function get(
		\Spameri\Elastic\Entity\Property\ElasticId $id
	) : \Spameri\Elastic\Entity\ElasticEntityInterface
	{
		return parent::get($id);
	}


	/**
	 * @param \Spameri\ElasticQuery\ElasticQuery $elasticQuery
	 * @return \Spameri\Elastic\Entity\ElasticEntityInterface|\SpameriTests\Elastic\Data\Entity\Video
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function getBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\ElasticEntityInterface
	{
		return parent::getBy($elasticQuery);
	}


	/**
	 * @param \Spameri\ElasticQuery\ElasticQuery $elasticQuery
	 * @return \Spameri\Elastic\Entity\ElasticEntityCollectionInterface|array<\SpameriTests\Elastic\Data\Entity\Video>
	 */
	public function getAllBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		return parent::getAllBy($elasticQuery);
	}
	
}
```
