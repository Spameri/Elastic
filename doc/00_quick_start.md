# Quick Start

This guide walks you through setting up Spameri/Elastic and creating your first searchable entity.

## 1. Install

Use composer to install this library:

```bash
composer require spameri/elastic
```

---

## 2. Configure

### I. Register Extension

In your configuration neon file, add the extension:

```neon
extensions:
    spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension
```

Optionally add a Symfony Console implementation for CLI commands:

```neon
extensions:
    console: Contributte\Console\DI\ConsoleExtension
```

### II. Configure ElasticSearch Connection

Tell the library where ElasticSearch is running. Default values are **localhost:9200**.

```neon
spameriElasticSearch:
    host: 127.0.0.1
    port: 9200
    debug: true  # Enable Tracy debug panel
    version: 8   # ElasticSearch version
```

---

## 3. Create Entity Class

Create an entity that extends `AbstractElasticEntity`:

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity;

class Product extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        public readonly int $databaseId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $price,
        public readonly string $availability,
    ) {
        parent::__construct($id);
    }
}
```

**Key Points:**
- Extend `AbstractElasticEntity` (implements `ElasticEntityInterface`)
- First constructor parameter must be `ElasticIdInterface`
- Use `readonly` properties for immutability
- The `entityVariables()` method is inherited and returns all properties automatically

---

## 4. Create Index Configuration

Create a mapping class that defines how the entity is stored in ElasticSearch:

```php
<?php declare(strict_types = 1);

namespace App\Model\Settings;

class ProductMapping implements \Spameri\Elastic\Settings\IndexConfigInterface
{
    public function __construct(
        private readonly string $indexName = 'product',
    ) {}

    public function provide(): \Spameri\ElasticQuery\Mapping\Settings
    {
        $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'databaseId',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        );

        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'name',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
            )
        );

        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'description',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
            )
        );

        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'price',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_FLOAT
            )
        );

        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'availability',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        );

        return $settings;
    }

    public function entityClass(): array
    {
        return [\App\Model\Entity\Product::class];
    }

    public function indexName(): string
    {
        return $this->indexName;
    }
}
```

Register it in your configuration:

```neon
services:
    - App\Model\Settings\ProductMapping
```

---

## 5. Create the Index

Run the console command to create the index:

```bash
php bin/console spameri:elastic:initialize-index
```

Or for a specific index:

```bash
php bin/console spameri:elastic:initialize-index product
```

---

## 6. Save Data

Use the `EntityManager` to persist entities:

```php
<?php declare(strict_types = 1);

namespace App\Service;

class ProductService
{
    public function __construct(
        private readonly \Spameri\Elastic\EntityManager $entityManager,
    ) {}

    public function createProduct(
        int $databaseId,
        string $name,
        ?string $description,
        float $price,
        string $availability,
    ): \App\Model\Entity\Product
    {
        $product = new \App\Model\Entity\Product(
            new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
            $databaseId,
            $name,
            $description,
            $price,
            $availability,
        );

        $this->entityManager->persist($product);

        return $product;
    }
}
```

**Key Points:**
- Use `EmptyElasticId` for new entities
- After `persist()`, the entity has a real `ElasticId` assigned by ElasticSearch

---

## 7. Retrieve Data

### Get by ID

```php
$product = $this->entityManager->find(
    \App\Model\Entity\Product::class,
    new \Spameri\Elastic\Entity\Property\ElasticId('abc123'),
);
```

### Get All

```php
$products = $this->entityManager->findAll(\App\Model\Entity\Product::class);

foreach ($products as $product) {
    echo $product->name . PHP_EOL;
}
```

### Query with Filters

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->query()->addMust(
    new \Spameri\ElasticQuery\Query\Term('availability', 'in_stock')
);

$products = $this->entityManager->findBy(
    \App\Model\Entity\Product::class,
    $elasticQuery,
);
```

---

## 8. Search

### Simple Text Search

```php
public function search(string $queryString): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
{
    $elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();

    $elasticQuery->query()->addShould(
        new \Spameri\ElasticQuery\Query\Match\Match(
            'name',
            $queryString
        )
    );

    $elasticQuery->query()->addShould(
        new \Spameri\ElasticQuery\Query\Match\Match(
            'description',
            $queryString
        )
    );

    return $this->entityManager->findBy(
        \App\Model\Entity\Product::class,
        $elasticQuery,
    );
}
```

### Advanced Search with Fuzzy Matching

```php
public function advancedSearch(string $queryString): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
{
    $elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();

    // Fuzzy match on name (handles typos)
    $elasticQuery->query()->addShould(
        new \Spameri\ElasticQuery\Query\Match\Match(
            'name',
            $queryString,
            3, // boost
            \Spameri\ElasticQuery\Query\Match\Operator::OR,
            new \Spameri\ElasticQuery\Query\Match\Fuzziness(
                \Spameri\ElasticQuery\Query\Match\Fuzziness::AUTO
            )
        )
    );

    // Wildcard for partial matches
    $elasticQuery->query()->addShould(
        new \Spameri\ElasticQuery\Query\WildCard(
            'name',
            $queryString . '*',
            1 // boost
        )
    );

    // Exact phrase match (higher score)
    $elasticQuery->query()->addShould(
        new \Spameri\ElasticQuery\Query\MatchPhrase(
            'name',
            $queryString,
            5 // boost
        )
    );

    return $this->entityManager->findBy(
        \App\Model\Entity\Product::class,
        $elasticQuery,
    );
}
```

---

## 9. Use in Presenter

```php
<?php declare(strict_types = 1);

namespace App\Presenter;

class ProductPresenter extends \Nette\Application\UI\Presenter
{
    public function __construct(
        private readonly \Spameri\Elastic\EntityManager $entityManager,
    ) {
        parent::__construct();
    }

    public function renderList(?string $query = null): void
    {
        if ($query !== null && $query !== '') {
            $elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
            $elasticQuery->query()->addShould(
                new \Spameri\ElasticQuery\Query\Match\Match('name', $query)
            );
            $products = $this->entityManager->findBy(
                \App\Model\Entity\Product::class,
                $elasticQuery,
            );
        } else {
            $products = $this->entityManager->findAll(
                \App\Model\Entity\Product::class,
            );
        }

        $this->template->products = $products;
        $this->template->query = $query;
    }

    protected function createComponentSearchForm(): \Nette\Application\UI\Form
    {
        $form = new \Nette\Application\UI\Form();
        $form->addText('query', 'Search');
        $form->addSubmit('search', 'Search');

        $form->onSuccess[] = function (\Nette\Application\UI\Form $form): void {
            $this->redirect('this', ['query' => $form->getValues()->query]);
        };

        return $form;
    }
}
```

---

## 10. Bulk Import (Optional)

For importing large amounts of data, use the Import system:

```php
<?php declare(strict_types = 1);

namespace App\Import;

class ProductDataProvider implements \Spameri\Elastic\Import\DataProviderInterface
{
    public function __construct(
        private readonly \Nette\Database\Explorer $database,
    ) {}

    public function provide(\Spameri\Elastic\Import\Run\Options $options): \Generator
    {
        $offset = 0;
        $limit = 100;

        while (true) {
            $items = $this->database->table('products')
                ->limit($limit, $offset)
                ->fetchAll();

            if (empty($items)) {
                break;
            }

            yield from $items;

            $offset += $limit;
        }
    }
}
```

```php
<?php declare(strict_types = 1);

namespace App\Import;

class ProductPrepareData implements \Spameri\Elastic\Import\PrepareImportDataInterface
{
    public function prepare(mixed $entityData): \App\Model\Entity\Product
    {
        return new \App\Model\Entity\Product(
            new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
            $entityData->id,
            $entityData->name,
            $entityData->description,
            (float) $entityData->price,
            $entityData->availability,
        );
    }
}
```

See [Import System](15_import_system.md) for complete documentation.

---

## Summary

| Step | What You Do |
|------|-------------|
| 1. Install | `composer require spameri/elastic` |
| 2. Configure | Add extension and settings to neon |
| 3. Entity | Create class extending `AbstractElasticEntity` |
| 4. Mapping | Create `IndexConfigInterface` implementation |
| 5. Index | Run `spameri:elastic:initialize-index` |
| 6. Save | Use `EntityManager->persist()` |
| 7. Query | Use `EntityManager->find/findBy/findAll()` |

---

## Next Steps

- [Configuration](02_configuration.md) - All configuration options
- [Entity Class Guide](03_entity_class.md) - Detailed entity creation
- [Index Mapping](05_new_index_with_mapping.md) - Advanced mapping options
- [Advanced Queries](13_advanced_get.md) - Complex query examples
- [EntityManager](17_entity_manager.md) - Full API reference
