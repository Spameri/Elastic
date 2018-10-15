# Entity class

Lets create entity class, continuing our example, in folder `app/ProductModule/Entity/Product.php` given file contents:
```php
namespace App\ProductModule\Entity;


class Product implements \Spameri\Elastic\Entity\IElasticEntity
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

- Entity is in our defined namespace for `ProductModule` in own folder `Entity` which is shared for multiple entities.
- Class extends interface `\Spameri\Elastic\Entity\IElasticEntity`, this is core interface for ElasticSearch document.
It has to have `id` provided by ElasticSearch, library takes care of handling this field, no need to add in mapping.
- Based on this interface, library figures out how to save this class. 
- `__construct` accepts all data from ElasticSearch or EntityFactory, this is where you need to specify all class parameters.
- `id` property should be in all classes of this interface.
- Interface requires function `id()` based on returned value it updates or creates entity.
- Interface requires `entityVariables()` in this exact form. (This may be changed in future versions, but now is required)

### Adding properties to product entity

#### Single value property - `name`

- Lets say our product has limited name length to maximum of 255 characters and also 0 characters is not enough to 
describe product.
- We do not have reliable data input so lets validate this with help of interface `\Spameri\Elastic\Entity\IValue`.
- First create value object for name `\App\ProductModule\Entity\Product\Name`. 
- Class should implement interface `\Spameri\Elastic\Entity\IValue`.
- `__construct` should have one parameter **string $value**.
- In construct do our validation for product name.
- Implement `value()` method.
- Add property to `\App\ProductModule\Entity\Product` constructor.
- Generate getter in `\App\ProductModule\Entity\Product` entity.
- Result:
```php
namespace App\ProductModule\Entity\Product;


class Name implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $value
	)
	{
		if (\strlen($value) < 0) {
			throw new \InvalidArgumentException('Empty string is not supported for product name: ' . $value);
		}
		if (\strlen($value) > 255) {
			$value = \substr($value, 0, 255);
		}

		$this->value = $value;
	}


	public function value() : string
	{
		return $this->value;
	}

}
```

#### Value collection property - `details.tags`
TODO

#### Single entity property - `details`
TODO

#### Entity collection property - `parameterValues`
TODO

#### ElasticEntity collection property - `details.accessories`
TODO
