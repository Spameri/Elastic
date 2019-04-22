# Entity class

Lets create entity class, continuing our example, in folder `tests/SpameriTests/Data/Entity/Video.php` given file contents:
```php
namespace SpameriTests\Data\Entity;


class Video implements \Spameri\Elastic\Entity\IElasticEntity
{

	/**
	 * @var \Spameri\Elastic\Entity\Property\IElasticId
	 */
	private $id;


	public function __construct(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	)
	{
		$this->id = $id;
	}


	public function id() : \Spameri\Elastic\Entity\Property\IElasticId
	{
		return $this->id;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}

}
```

### Lets look at class part by part.

- Entity is in our defined namespace in own folder `Entity` which is shared for multiple entities.
- Class extends interface `\Spameri\Elastic\Entity\IElasticEntity`, this is core interface for ElasticSearch document.
It has to have `id` provided by ElasticSearch, library takes care of handling this field, no need to add in mapping.
- Based on this interface, library figures out how to save this class. 
- `__construct` accepts all data from ElasticSearch or EntityFactory, this is where you need to specify all class parameters.
- `id` property should be in all classes of this interface.
- Interface requires function `id()` based on returned value it updates or creates entity.
- Interface requires `entityVariables()` in this exact form. (This may be changed in future versions, but now is required)

### Adding properties to video entity

#### Single value property - `Video.Story.KeyWord`

- Lets say our Video has limited keyword length to maximum of 55 characters and also 0 characters is not enough to 
describe keyword.
- We do not have reliable data input so lets validate this with help of interface `\Spameri\Elastic\Entity\IValue`.
- First create value object for keyword `\SpameriTests\Data\Entity\Video\Story\KeyWord`. 
- Class should implement interface `\Spameri\Elastic\Entity\IValue`.
- `__construct` should have one parameter **string $value**.
- In construct do our validation for keyword.
- Implement `value()` method.
- Add property to `\SpameriTests\Data\Entity\Video` constructor.
- Generate getter in `\SpameriTests\Data\Entity\Video` entity for KeyWord. 
- Result:
```php
namespace SpameriTests\Data\Entity\Video\Story;


class KeyWord implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $value
	)
	{
		if ($value === '') {
			throw new \InvalidArgumentException();
		}
		if (\strlen($value) > 55) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value() : string
	{
		return $this->value;
	}

}
```

#### Value collection property - `Video.Story.KeyWordCollection`
TODO Description
```php
namespace SpameriTests\Data\Entity\Video\Story;


class KeyWordCollection implements \Spameri\Elastic\Entity\IValueCollection
{

	/**
	 * @var array<\SpameriTests\Data\Entity\Video\Story\KeyWord>
	 */
	private $collection;


	public function __construct(
		KeyWord ... $entities
	)
	{
		$this->collection = [];
		foreach ($entities as $keyWord) {
			$this->add($keyWord);
		}
	}


	public function add(
		KeyWord $keyWord
	) : void
	{
		$this->collection[$keyWord->value()] = $keyWord;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}
```

#### Single entity property - `Video.Story`
TODO Description
```php
namespace SpameriTests\Data\Entity\Video;


class Story implements \Spameri\Elastic\Entity\IEntity
{

	/**
	 * @var \SpameriTests\Data\Entity\Video\Story\KeyWordCollection
	 */
	private $keyWords;


	public function __construct(
		\SpameriTests\Data\Entity\Video\Story\KeyWordCollection $keyWord
	)
	{
		$this->keyWords = $keyWord;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function key() : string
	{

	}
}
```

#### Entity collection property - `Video.Connections.FollowsCollection`
TODO Description
```php
namespace SpameriTests\Data\Entity\Video\Connections;


class FollowsCollection extends \Spameri\Elastic\Entity\Collection\EntityCollection
{

}
```

#### ElasticEntity collection property - `Video.People`
TODO Description
```php
namespace SpameriTests\Data\Entity\Video;


class People extends \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
{

	public function personByImdb(
		\SpameriTests\Data\Entity\Property\ImdbId $imdb
	) : ?\SpameriTests\Data\Entity\Person
	{
		/** @var \SpameriTests\Data\Entity\Person $entity */
		foreach ($this->collection() as $entity) {
			if ($imdb->value() === $entity->identification()->imdb()->value()) {
				return $entity;
			}
		}

		return NULL;
	}
}
```

