# Entity class

Lets create entity class, continuing our example, in folder `tests/SpameriTests/Data/Entity/Video.php` given file contents:
```php
namespace SpameriTests\Elastic\Data\Entity;


class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id
	)
	{
		parent::__construct($id);
	}

}
```

### Lets look at class part by part.

- Entity is in our defined namespace in own folder `Entity` which is shared for multiple entities.
- Class extends `\Spameri\Elastic\Entity\AbstractElasticEntity` which implements `ElasticEntityInterface`. This is the base class for all ElasticSearch document entities.
- The abstract class provides `id()` and `entityVariables()` methods, so you don't need to implement them manually.
- Entity has `id` property (managed by the abstract class) provided by ElasticSearch - library takes care of handling this field, no need to add in mapping.
- Based on this inheritance, library figures out how to save this class.
- `__construct` accepts all data from ElasticSearch or EntityFactory - this is where you need to specify all class parameters. Always call `parent::__construct($id)` to initialize the ID.

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

#### Value collection property - `Video.Story.keyWords`
- For arrays of value objects, use typed arrays directly.
- No need to create custom collection classes - use `array<ValueInterface>` with PHPDoc annotations.
- The library will serialize arrays of `ValueInterface` objects automatically.

```php
namespace SpameriTests\Elastic\Data\Entity\Video;


class Story implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		/** @var array<\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord> */
		private array $keyWords = [],
	)
	{
	}


	/**
	 * @return array<\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord>
	 */
	public function keyWords(): array
	{
		return $this->keyWords;
	}


	public function addKeyWord(
		\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord $keyWord
	): void
	{
		$this->keyWords[] = $keyWord;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return \md5(\implode('_', $this->entityVariables()));
	}
}
```

#### Single entity property - `Video.Story`
- If you need some nested structure `\Spameri\Elastic\Entity\EntityInterface` interface is here for you.
- Also when feeling lazy there is `\Spameri\Elastic\Entity\AbstractEntity` for you to extend with methods implemented.
- In our example we have entity **Story** to encapsulate keywords and other story related properties.
- Library then can convert this entity to array and save it as array with no more help.
- Note: The `Story` class shown above in the value collection example demonstrates this pattern.

#### Entity collection property - `Video.Connections.follows`
- ElasticSearch is powerful tool and it allows you to nest objects and collection as you need, so you can make collection of nested objects.
- Use `\Spameri\Elastic\Entity\Collection\EntityCollection` directly - no need to create custom collection classes.
- Mark the property with `#[\Spameri\Elastic\Mapping\Collection]` attribute.

```php
namespace SpameriTests\Elastic\Data\Entity\Video;


class Connections implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Collection]
		private \Spameri\Elastic\Entity\Collection\EntityCollection $follows,
	)
	{
	}


	public function follows(): \Spameri\Elastic\Entity\Collection\EntityCollection
	{
		return $this->follows;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return 'connections';
	}
}
```

#### ElasticEntity collection property - `Video.people`
- `\Spameri\Elastic\Entity\Collection\ElasticEntityCollection` provides basic relations for entities in ElasticSearch.
- It saves **_id** to current entity as reference in raw data but when loaded you have full entity with that id.
Any changes made to related entity/ies will be persisted when main entity is saved.
- Entity can be manually related 1:1 with manual lazy load in Factory (example in [factory](11_entity_factory.md) documentation)
- Or multiple entities can be in collection lazily loaded all at once, also in factory example.
- Use `\Spameri\Elastic\Entity\Collection\ElasticEntityCollection` directly with the `#[\Spameri\Elastic\Mapping\ElasticCollection]` attribute.
- If you need custom query methods (like finding by specific property), put them in a service class or in the entity itself.

```php
// In your Video entity constructor:
#[\Spameri\Elastic\Mapping\ElasticCollection(class: \SpameriTests\Elastic\Data\Entity\Person::class)]
private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $people,
```

Example getter:
```php
public function people(): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
{
	return $this->people;
}
```

## Final product

A complete Video entity using concrete collection classes:

```php
namespace SpameriTests\Elastic\Data\Entity;


class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,

		#[\Spameri\Elastic\Mapping\Entity(class: \SpameriTests\Elastic\Data\Entity\Video\Identification::class)]
		private \SpameriTests\Elastic\Data\Entity\Video\Identification $identification,

		private \SpameriTests\Elastic\Data\Entity\Property\Name $name,

		private \SpameriTests\Elastic\Data\Entity\Property\Year $year,

		#[\Spameri\Elastic\Mapping\Entity(class: \SpameriTests\Elastic\Data\Entity\Video\Technical::class)]
		private \SpameriTests\Elastic\Data\Entity\Video\Technical $technical,

		#[\Spameri\Elastic\Mapping\Entity(class: \SpameriTests\Elastic\Data\Entity\Video\Story::class)]
		private \SpameriTests\Elastic\Data\Entity\Video\Story $story,

		#[\Spameri\Elastic\Mapping\Entity(class: \SpameriTests\Elastic\Data\Entity\Video\Details::class)]
		private \SpameriTests\Elastic\Data\Entity\Video\Details $details,

		#[\Spameri\Elastic\Mapping\Entity(class: \SpameriTests\Elastic\Data\Entity\Video\HighLights::class)]
		private \SpameriTests\Elastic\Data\Entity\Video\HighLights $highLights,

		#[\Spameri\Elastic\Mapping\Entity(class: \SpameriTests\Elastic\Data\Entity\Video\Connections::class)]
		private \SpameriTests\Elastic\Data\Entity\Video\Connections $connections,

		#[\Spameri\Elastic\Mapping\Collection]
		private \Spameri\Elastic\Entity\Collection\EntityCollection $seasons,

		#[\Spameri\Elastic\Mapping\ElasticCollection(class: \SpameriTests\Elastic\Data\Entity\Person::class)]
		private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $people,
	)
	{
		parent::__construct($id);
	}


	public function identification(): \SpameriTests\Elastic\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Elastic\Data\Entity\Property\Name $name): void
	{
		$this->name = $name;
	}


	public function year(): \SpameriTests\Elastic\Data\Entity\Property\Year
	{
		return $this->year;
	}


	public function setYear(\SpameriTests\Elastic\Data\Entity\Property\Year $year): void
	{
		$this->year = $year;
	}


	public function technical(): \SpameriTests\Elastic\Data\Entity\Video\Technical
	{
		return $this->technical;
	}


	public function story(): \SpameriTests\Elastic\Data\Entity\Video\Story
	{
		return $this->story;
	}


	public function details(): \SpameriTests\Elastic\Data\Entity\Video\Details
	{
		return $this->details;
	}


	public function highLights(): \SpameriTests\Elastic\Data\Entity\Video\HighLights
	{
		return $this->highLights;
	}


	public function connections(): \SpameriTests\Elastic\Data\Entity\Video\Connections
	{
		return $this->connections;
	}


	public function seasons(): \Spameri\Elastic\Entity\Collection\EntityCollection
	{
		return $this->seasons;
	}


	public function people(): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
	{
		return $this->people;
	}
}
```
