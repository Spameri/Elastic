# Quick Start

## 1. Install

Use composer to install this library.
```bash
composer require spameri/elastic
```

---

## 2. Configure

You need to set up few things first, before you can dive into ElasticSearch.

### I. Register extension

In your configuration neon file you need to add these lines to `extension:` section.

```yaml
extensions:
	spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension
	console: Kdyby\Console\DI\ConsoleExtension
	monolog: Kdyby\Monolog\DI\MonologExtension
```

### II. Configure

Now you need to tell library where is ElasticSearch running. Default values are **localhost**
and port **9200**. That means if you are running ElasticSearch locally with default port, no
need to configure anything. 

```yaml
spameriElasticSearch:
	host: 192.168.0.14
	port: 9200
```

### III. Configure Entity

Next step is to configure your first entity. This entity is for e-shop product.

```yaml
1.|	spameriElasticSearch:
2.|		entities:
3.|			SimpleProduct:
4.|				index: spameri_simple_product
5.|				dynamic: strict
6.|				config: @simpleProductConfig
7.|				properties: 
```
- First line is extensionName
- Second line is entities config array
- Third line is EntityName
- Fourth line is index name for this entity
- Fifth line is for specifying whether index should accept new not specified fields
- Sixth line is reference to where is object with entity configuration
- Seventh line is where you can configure your entity within this neon

---

## 3. Create entity class

```php
<?php declare(strict_types = 1);

class SimpleProduct implements \Spameri\Elastic\Entity\ElasticEntityInterface
{
	
	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		int $databaseId,
		string $name,
		?string $content,
		string $alias,
		string $image,
		float $price,
		string $availability,
		array $tags,
		array $categories
	)
	{
		// ...
	}
	
}
```

```php
public function id(): \Spameri\Elastic\Entity\Property\ElasticIdInterface
{
	return $this->id;
}


public function entityVariables(): array
{
	return \get_object_vars($this);
}
```

### Factory
````php
class SimpleProductFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	public function create(\Spameri\ElasticQuery\Response\Result\Hit $hit) : \Generator
	{
		yield new \App\ProductModule\Entity\SimpleProduct(
			new \Spameri\Elastic\Entity\Property\ElasticId($hit->id()),
			$hit->getValue('databaseId'),
			$hit->getValue('name'),
			$hit->getValue('content'),
			$hit->getValue('alias'),
			$hit->getValue('image'),
			$hit->getValue('price'),
			$hit->getValue('availability'),
			$hit->getValue('tags'),
			$hit->getValue('categories')
		);
	}

}
````

### CollectionFactory
````php
class SimpleProductCollectionFactory implements \Spameri\Elastic\Factory\CollectionFactoryInterface
{

	public function create(
		\Spameri\Elastic\Model\ServiceInterface $service
		, array $elasticIds = []
		, \Spameri\Elastic\Entity\ElasticEntityInterface ... $entityCollection
	) : \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		return new \App\ProductModule\Entity\ProductCollection($service, $elasticIds, ... $entityCollection);
	}

}
````

## 4. Index Configuring
````php
class SimpleProductConfig implements \Spameri\Elastic\Settings\IndexConfigInterface
{
	public function __construct(
		string $indexName
	)
	{
		$this->indexName = $indexName;
	}
}
````

`public function provide(): \Spameri\ElasticQuery\Mapping\Settings`

````php
$settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);
$czechDictionary = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\CzechDictionary();
$settings->addAnalyzer($czechDictionary);

$lowerCase = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\Lowercase();
$settings->addAnalyzer($lowerCase);
````

````php
$settings->addMappingField(
	new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
		'databaseId',
		\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
	)
);
$settings->addMappingField(
	new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
		'name',
		\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
		$czechDictionary
	)
);
$settings->addMappingField(
	new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
		'content',
		\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
		$czechDictionary
	)
);
````


````php
$settings->addMappingField(
	new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
		'tags',
		\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
		$lowerCase
	)
);
````


## 5. Export data to ElasticSearch

````php
class ExportToElastic extends \Spameri\Elastic\Import\Run
{

	public function __construct(
		string $logDir = 'log',
		\Symfony\Component\Console\Output\ConsoleOutput $output,
		\Spameri\Elastic\Import\Run\NullLoggerHandler $loggerHandler,
		\Spameri\Elastic\Import\Lock\NullLock $lock,
		\Spameri\Elastic\Import\RunHandler\NullHandler $runHandler,

		\App\ProductModule\Model\ExportToElastic\DataProvider $dataProvider,
		\App\ProductModule\Model\ExportToElastic\PrepareImportData $prepareImportData,
		\App\ProductModule\Model\ExportToElastic\DataImport $dataImport,

		\Spameri\Elastic\Import\AfterImport\NullAfterImport $afterImport
	)
	{
		parent::__construct($logDir, $output, $loggerHandler, $lock, $runHandler, $dataProvider, $prepareImportData, $dataImport, $afterImport);
	}

}
````


````php
class DataProvider implements \Spameri\Elastic\Import\DataProviderInterface
{
	public function provide(\Spameri\Elastic\Import\Run\Options $options): \Generator
	{
		$query = $this->connection->select('*')->from('table');
		
		while ($hasResults) {
			$items = $query->fetchAll($offset, $limit);

			yield from $items;

			if ( ! \count($items)) {
				$hasResults = FALSE;

			} else {
				$offset += $limit;
			}
		}
	}
}	
````


````php

class PrepareImportData implements \Spameri\Elastic\Import\PrepareImportDataInterface
{

	public function prepare($entityData): \Spameri\Elastic\Entity\AbstractImport
	{
		$imageSrc = '//via.placeholder.com/150x150';
		$elasticId = NULL;
		$tags = [];
		$categories = [];
		return new \App\ProductModule\Entity\SimpleProduct(
			$elasticId,
			$entityData['id'],
			$entityData['name'],
			$entityData['content_description'],
			$entityData['alias'],
			$imageSrc,
			$entityData['amount'],
			$entityData['availability_id'] === 1 ? 'Skladem' : 'Nedostupné',
			$tags,
			$categories
		);
	}

}
````

````php
class DataImport implements \Spameri\Elastic\Import\DataImportInterface
{

	/**
	 * @param \App\ProductModule\Entity\SimpleProduct $entity
	 */
	public function import(
		\Spameri\Elastic\Entity\AbstractImport $entity
	): \Spameri\Elastic\Import\ResponseInterface
	{
		$id = $this->productService->insert($entity);

		return new \Spameri\Elastic\Import\Response\SimpleResponse(
			$id,
			$entity
		);
	}

}
````

````php
$options = new \Spameri\Elastic\Import\Run\Options(600);

// Clear index
try {
	$this->delete->execute($this->simpleProductConfig->provide()->indexName());
} catch (\Spameri\Elastic\Exception\AbstractElasticSearchException $exception) {}

// Create index
$this->create->execute(
	$this->simpleProductConfig->provide()->indexName(),
	$this->simpleProductConfig->provide()->toArray()
);

// Export
$this->exportToElastic->execute($options);
````

## 6. Presenter, Form, Template
````php
class SimpleProductListPresenter extends \App\Presenter\BasePresenter
{
	
	public function renderDefault($queryString): void
	{
		$query = $this->buildQuery($queryString);

		try {
			$products = $this->productService->getAllBy($query);

		} catch (\Spameri\Elastic\Exception\AbstractElasticSearchException $exception) {
			$products = [];
		}

		$this->getTemplate()->add(
			'products',
			$products
		);
		$this->getTemplate()->add(
			'queryString',
			$queryString
		);
	}

}
````

````php
public function createComponentSearchForm() :\Nette\Application\UI\Form
{
	$form = new \Nette\Application\UI\Form();
	$form->addText('queryString', 'query')
		->setAttribute('class', 'inp-text suggest')
	;

	$form->addSubmit('search', 'Search');

	$form->onSuccess[] = function () use ($form) {
		$this->redirect(
			301,
			':Product:SimpleProductList:default',
			[
				'queryString' => $form->getValues()->queryString,
			]
		);
	};

	return $form;
}
````

````php
{control searchForm}
<h2>You have searched: {$queryString}</h2>

<div class="product-list products-list-full">
	<ul class="reset products full-products">
	{foreach $products as $product}
		<li>
			<div class="spc">
				<a href="//benu.cz/{$product->getAlias()}" class="detail" style="height: 321px">
					<h2 class="title">
						<span class="img">
							<img class="lazy lazy-loaded" src="{$product->getImage()}" width="180" height="156" alt="{$product->getName()}">
						</span>
						<span class="name" style="height: 48px;">{$product->getName()|truncate:40}</span>
					</h2>
````

## 7. Search

```php
public function buildQuery(?string $queryString): \Spameri\ElasticQuery\ElasticQuery
{
	$query = new \Spameri\ElasticQuery\ElasticQuery();
	$query->addShouldQuery(
		new \Spameri\ElasticQuery\Query\Match(
			'name',
			$queryString
		)
	);
	
	return $query;
}
```

````php
$products = $this->productService->getAllBy($query);
````

## 8. Fine Tuning

````php
$subQuery = new \Spameri\ElasticQuery\Query\QueryCollection();
````

```php
$subQuery->addShouldQuery(
	new \Spameri\ElasticQuery\Query\Match(
		'name',
		$queryString,
		3,
		\Spameri\ElasticQuery\Query\Match\Operator::OR,
		new \Spameri\ElasticQuery\Query\Match\Fuzziness(\Spameri\ElasticQuery\Query\Match\Fuzziness::AUTO)
	)
);
$subQuery->addShouldQuery(
	new \Spameri\ElasticQuery\Query\WildCard(
		'name',
		$queryString . '*',
		1
	)
);
$subQuery->addShouldQuery(
	new \Spameri\ElasticQuery\Query\MatchPhrase(
		'name',
		$queryString,
		1
	)
);
$subQuery->addShouldQuery(
	new \Spameri\ElasticQuery\Query\Match(
		'content',
		$queryString,
		1,
		\Spameri\ElasticQuery\Query\Match\Operator:: OR,
		new \Spameri\ElasticQuery\Query\Match\Fuzziness(\Spameri\ElasticQuery\Query\Match\Fuzziness::AUTO)
	)
);
```


