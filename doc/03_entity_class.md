# Entity class

Lets create entity class, continuing our example, in folder `tests/SpameriTests/Data/Entity/Video.php` given file contents:
```php
namespace SpameriTests\Elastic\Data\Entity;


class Video implements \Spameri\Elastic\Entity\ElasticEntityInterface
{

	private \Spameri\Elastic\Entity\Property\ElasticIdInterface $id;


	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id
	)
	{
		$this->id = $id;
	}


	public function id() : \Spameri\Elastic\Entity\Property\ElasticIdInterface
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
- Class extends interface `\Spameri\Elastic\Entity\ElasticEntityInterface`, this is core interface for ElasticSearch document.
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
- We do not have reliable data input so lets validate this with help of interface `\Spameri\Elastic\Entity\ValueInterface`.
- First create value object for keyword `\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord`. 
- Class should implement interface `\Spameri\Elastic\Entity\ValueInterface`.
- `__construct` should have one parameter **string $value**.
- In construct do our validation for keyword.
- Implement `value()` method.
- Add property to `\SpameriTests\Elastic\Data\Entity\Video` constructor.
- Generate getter in `\SpameriTests\Elastic\Data\Entity\Video` entity for KeyWord. 
- Result:
```php
namespace SpameriTests\Elastic\Data\Entity\Video\Story;


class KeyWord implements \Spameri\Elastic\Entity\ValueInterface
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
- If you need array of scalar values lets create ValueCollection.
- For easy setup you can use `\Spameri\Elastic\Entity\AbstractValueCollection` just create your collection and extend this abstract as you need.
For more advanced and typed approach use interface, as described next.
- Interface `\Spameri\Elastic\Entity\ValueCollectionInterface` is when you want typed and validated scalar value collection.
- After implementing interface you need to implement **getIterator()** method.
- Next to be type save you want to add methods **add**, **remove**, **get**, **__construct**
- For **__construct** you best fill values to collection as here [\Spameri\Elastic\Entity\AbstractValueCollection#L20](../src/Entity/AbstractValueCollection.php#L20)
```php
namespace SpameriTests\Elastic\Data\Entity\Video\Story;


class KeyWordCollection implements \Spameri\Elastic\Entity\ValueCollectionInterface
{

	/**
	 * @var array<\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord>
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
		\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord $keyWord
	) : void
	{
		$this->collection[$keyWord->value()] = $keyWord;
	}


	public function remove(string $key) : void
	{
		unset($this->collection[$key]);
	}


	public function get(string $key) : ?\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord
	{
		if ( ! isset($this->collection[$key])) {
			return NULL;
		}

		return $this->collection[$key];
	}

	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}
```

#### Single entity property - `Video.Story`
- If you need some nested structure `\Spameri\Elastic\Entity\EntityInterface` interface is here for you.
- Also when feeling lazy there is `\Spameri\Elastic\Entity\AbstractEntity` for you to extend with methods implemented.
- In our example we have entity **Story** to encapsulate keywords and other story related properties.
- Library then can convert this entity to array and save it as array with no more help.
 
```php
namespace SpameriTests\Elastic\Data\Entity\Video;


class Story implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection
	 */
	private $keyWords;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection $keyWord
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
		return \md5(\implode('_', $this->entityVariables()));
	}
}
```

#### Entity collection property - `Video.Connections.FollowsCollection`
- ElasticSearch is powerful tool and it allows you to nest objects and collection as you need, so you can make collection of nested objects.
- This is simple you have Entity **Story** with implemented `EntityInterface` interface and all you need is create collection, extend `class FollowsCollection extends \Spameri\Elastic\Entity\Collection\EntityCollection`
and you are done.
```php
namespace SpameriTests\Elastic\Data\Entity\Video\Connections;


class FollowsCollection extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection
{

}
```

#### ElasticEntity collection property - `Video.People`
- `\Spameri\Elastic\Entity\Collection\ElasticEntityCollection` provides basic relations for entities in ElasticSearch.
- It saves **_id** to current entity as reference in raw data but when loaded you have full entity with that id. 
Any changes made to related entity/ies will be persisted when main entity is saved.
- Entity can be manually related 1:1 with manual lazy load in Factory (example in [factory](11_entity_factory.md) documentation)
- Or multiple entities can be in collection lazily loaded all at once, also in factory example.
- All you need is extend `\Spameri\Elastic\Entity\Collection\ElasticEntityCollection` and fill with your entities, library will do saving and resolving for you.  
```php
namespace SpameriTests\Elastic\Data\Entity\Video;


class People extends \Spameri\Elastic\Entity\Collection\AbstractElasticEntityCollection
{

	public function personByImdb(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $imdb
	) : ?\SpameriTests\Elastic\Data\Entity\Person
	{
		/** @var \SpameriTests\Elastic\Data\Entity\Person $entity */
		foreach ($this->collection() as $entity) {
			if ($imdb->value() === $entity->identification->imdb->value()) {
				return $entity;
			}
		}

		return NULL;
	}
}
```

## Final product [Example](../tests/SpameriTests/Data/Entity/Video.php)
```php
namespace SpameriTests\Elastic\Data\Entity;


class Video implements \Spameri\Elastic\Entity\ElasticEntityInterface
{

	/**
	 * @var \Spameri\Elastic\Entity\Property\ElasticIdInterface
	 */
	private $id;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Identification
	 */
	private $identification;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Year
	 */
	private $year;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical
	 */
	private $technical;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story
	 */
	private $story;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Details
	 */
	private $details;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights
	 */
	private $highLights;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections
	 */
	private $connections;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection
	 */
	private $season;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\People
	 */
	private $people;


	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        \SpameriTests\Elastic\Data\Entity\Video\Identification $identification,
        \SpameriTests\Elastic\Data\Entity\Property\Name $name,
        \SpameriTests\Elastic\Data\Entity\Property\Year $year,
        \SpameriTests\Elastic\Data\Entity\Video\Technical $technical,
        \SpameriTests\Elastic\Data\Entity\Video\Story $story,
        \SpameriTests\Elastic\Data\Entity\Video\Details $details,
        \SpameriTests\Elastic\Data\Entity\Video\HighLights $highLights,
        \SpameriTests\Elastic\Data\Entity\Video\Connections $connections,
        \SpameriTests\Elastic\Data\Entity\Video\People $people,
        \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection $season = NULL
	)
	{
		$this->id = $id;
		$this->identification = $identification;
		$this->name = $name;
		$this->year = $year;
		$this->technical = $technical;
		$this->story = $story;
		$this->details = $details;
		$this->highLights = $highLights;
		$this->connections = $connections;

		if ($season === NULL) {
			$season = new \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection();
		}
		$this->season = $season;
		$this->people = $people;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function id() : \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->id;
	}


	public function identification() : \SpameriTests\Elastic\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function name() : \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Elastic\Data\Entity\Property\Name $name) : void
	{
		$this->name = $name;
	}


	public function year() : \SpameriTests\Elastic\Data\Entity\Property\Year
	{
		return $this->year;
	}


	public function setYear(\SpameriTests\Elastic\Data\Entity\Property\Year $year) : void
	{
		$this->year = $year;
	}


	public function technical() : \SpameriTests\Elastic\Data\Entity\Video\Technical
	{
		return $this->technical;
	}


	public function setTechnicalFromImdb(\SpameriTests\Elastic\Data\Entity\Video\Technical $technical) : void
	{
		$this->technical = $technical;
	}


	public function story() : \SpameriTests\Elastic\Data\Entity\Video\Story
	{
		return $this->story;
	}


	public function details() : \SpameriTests\Elastic\Data\Entity\Video\Details
	{
		return $this->details;
	}


	public function highLights() : \SpameriTests\Elastic\Data\Entity\Video\HighLights
	{
		return $this->highLights;
	}


	public function connections() : \SpameriTests\Elastic\Data\Entity\Video\Connections
	{
		return $this->connections;
	}


	public function season() : \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection
	{
		return $this->season;
	}


	public function people() : \SpameriTests\Elastic\Data\Entity\Video\People
	{
		return $this->people;
	}
}
```
