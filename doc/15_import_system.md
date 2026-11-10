# Import System

## Overview

The Import System provides a robust framework for bulk importing data into ElasticSearch from external sources (databases, APIs, CSV files, etc.). It includes:

- **Progress tracking** with console progress bars
- **Locking mechanism** to prevent concurrent imports
- **Logging** for debugging and audit trails
- **Error handling** with typed exceptions
- **Validation value objects** for type-safe data preparation
- **Post-import hooks** for cleanup/notifications

The import workflow follows these steps:

1. **Lock** - Prevent concurrent imports
2. **Provide** - Fetch data from source (generator for memory efficiency)
3. **Prepare** - Transform and validate data
4. **Import** - Index to ElasticSearch
5. **After** - Post-processing (optional)
6. **Release** - Release lock

---

## Quick Start

### 1. Create Data Provider

Implements `DataProviderInterface` to fetch data from your source:

```php
<?php

namespace App\Import;

class ProductDataProvider implements \Spameri\Elastic\Import\DataProviderInterface
{
    public function __construct(
        private \Nette\Database\Explorer $database
    ) {}

    public function count(\Spameri\Elastic\Import\Run\Options $options): int
    {
        return $this->database
            ->table('products')
            ->where('active', true)
            ->count();
    }

    public function provide(\Spameri\Elastic\Import\Run\Options $options): \Generator
    {
        $offset = 0;
        $limit = 100;

        while (true) {
            $items = $this->database
                ->table('products')
                ->where('active', true)
                ->limit($limit, $offset)
                ->fetchAll();

            if (count($items) === 0) {
                break;
            }

            foreach ($items as $item) {
                yield $item;
            }

            $offset += $limit;
        }
    }
}
```

**Key Points:**
- Use `yield` for memory efficiency with large datasets
- `count()` enables progress bar calculation
- Paginate your queries to avoid loading everything into memory

---

### 2. Create Import Data Class

Extends `AbstractImport` and uses validation value objects:

```php
<?php

namespace App\Import;

class ProductImport extends \Spameri\Elastic\Entity\AbstractImport
{
    public function __construct(
        int $key, // Unique identifier for logging
        private \Spameri\Elastic\Entity\Import\StringValue $name,
        private \Spameri\Elastic\Entity\Import\StringValue $description,
        private \Spameri\Elastic\Entity\Import\FloatValue $price,
        private \Spameri\Elastic\Entity\Import\IntegerValue $stock,
        private \Spameri\Elastic\Entity\Import\BoolValue $inStock,
        private \Spameri\Elastic\Entity\Import\ArrayValue $tags,
    ) {
        parent::__construct($key);
    }
}
```

**Alternative:** Property can be `NoValue` to omit from output:

```php
public function __construct(
    int $key,
    private \Spameri\Elastic\Entity\Import\StringValue $name,
    private \Spameri\Elastic\Entity\Import\NoValue $obsoleteField,
) {
    parent::__construct($key);
}
```

The `obsoleteField` won't appear in the output array.

---

### 3. Create Prepare Class

Implements `PrepareImportDataInterface` to transform source data:

```php
<?php

namespace App\Import;

class PrepareProductData implements \Spameri\Elastic\Import\PrepareImportDataInterface
{
    /**
     * @param \Nette\Database\Table\ActiveRow $entityData
     */
    public function prepare($entityData): \Spameri\Elastic\Entity\AbstractImport
    {
        // Validate and transform data
        $name = trim($entityData['name']);
        if (empty($name)) {
            throw new \Spameri\Elastic\Import\Exception\Omit('Product has no name');
        }

        $price = (float) $entityData['price'];
        if ($price < 0) {
            throw new \Spameri\Elastic\Import\Exception\Error('Price cannot be negative');
        }

        return new ProductImport(
            key: $entityData['id'],
            name: new \Spameri\Elastic\Entity\Import\StringValue(
                $name,
                'name'
            ),
            description: new \Spameri\Elastic\Entity\Import\StringValue(
                $entityData['description'] ?? '',
                'description'
            ),
            price: new \Spameri\Elastic\Entity\Import\FloatValue(
                $price,
                'price'
            ),
            stock: new \Spameri\Elastic\Entity\Import\IntegerValue(
                $entityData['stock'],
                'stock'
            ),
            inStock: new \Spameri\Elastic\Entity\Import\BoolValue(
                $entityData['stock'] > 0,
                'inStock'
            ),
            tags: new \Spameri\Elastic\Entity\Import\ArrayValue(
                explode(',', $entityData['tags'] ?? ''),
                'tags'
            ),
        );
    }
}
```

---

### 4. Create Data Import Class

Implements `DataImportInterface` to persist to ElasticSearch:

```php
<?php

namespace App\Import;

class ProductDataImport implements \Spameri\Elastic\Import\DataImportInterface
{
    public function __construct(
        private \App\Model\ProductService $productService
    ) {}

    /**
     * @param ProductImport $entity
     */
    public function import(
        \Spameri\Elastic\Entity\AbstractImport $entity
    ): \Spameri\Elastic\Import\ResponseInterface
    {
        // Convert to entity
        $product = new \App\Entity\Product(
            new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
            $entity->toArray()['name'],
            $entity->toArray()['price'],
            // ... map other fields
        );

        $id = $this->productService->insert($product);

        return new \Spameri\Elastic\Import\Response\SimpleResponse(
            $id,
            $entity
        );
    }
}
```

---

### 5. Create Run Class

Extends `Run` and wires dependencies:

```php
<?php

namespace App\Import;

class ProductImportRun extends \Spameri\Elastic\Import\Run
{
    public function __construct(
        string $logDir,
        \Spameri\Elastic\Import\LoggerHandlerInterface $loggerHandler,
        \Spameri\Elastic\Import\LockInterface $lock,
        \Spameri\Elastic\Import\RunHandlerInterface $runHandler,
        ProductDataProvider $dataProvider,
        PrepareProductData $prepareImportData,
        ProductDataImport $dataImport,
        \Spameri\Elastic\Import\AfterImportInterface $afterImport,
    ) {
        parent::__construct(
            $logDir,
            $loggerHandler,
            $lock,
            $runHandler,
            $dataProvider,
            $prepareImportData,
            $dataImport,
            $afterImport,
        );
    }
}
```

**Alternative:** Use `SimpleRun` if you don't need custom behavior.

---

### 6. Execute Import

```php
$options = new \Spameri\Elastic\Import\Run\Options(
    lockDuration: 3600 // Lock duration in seconds
);

$productImportRun->execute($options);
```

---

## Validation Value Objects

These objects provide type safety and automatic conversion in `AbstractImport::toArray()`.

### StringValue

Wraps a string value with field name.

```php
new \Spameri\Elastic\Entity\Import\StringValue(
    value: 'Product Name',
    key: 'name'
)
```

Output: `['name' => 'Product Name']`

---

### IntegerValue

Wraps an integer value.

```php
new \Spameri\Elastic\Entity\Import\IntegerValue(
    value: 42,
    key: 'quantity'
)
```

Output: `['quantity' => 42]`

---

### FloatValue

Wraps a float/decimal value.

```php
new \Spameri\Elastic\Entity\Import\FloatValue(
    value: 19.99,
    key: 'price'
)
```

Output: `['price' => 19.99]`

---

### BoolValue

Wraps a boolean value.

```php
new \Spameri\Elastic\Entity\Import\BoolValue(
    value: true,
    key: 'active'
)
```

Output: `['active' => true]`

---

### ArrayValue

Wraps an array value.

```php
new \Spameri\Elastic\Entity\Import\ArrayValue(
    value: ['electronics', 'gadgets'],
    key: 'categories'
)
```

Output: `['categories' => ['electronics', 'gadgets']]`

---

### DateValue

Wraps a date with automatic formatting.

```php
new \Spameri\Elastic\Entity\Import\DateValue(
    value: new \DateTime('2025-01-15'),
    key: 'publishedAt'
)
```

Output: `['publishedAt' => '2025-01-15T00:00:00']` (ISO 8601 format)

---

### EmptyValue

Represents an empty/null value (included in output).

```php
new \Spameri\Elastic\Entity\Import\EmptyValue(
    key: 'optionalField'
)
```

Output: `['optionalField' => null]`

---

### NoValue

Represents a field that should be omitted entirely from output.

```php
new \Spameri\Elastic\Entity\Import\NoValue()
```

Output: Field is NOT included in array at all.

**Use Case:** Legacy fields from source data that shouldn't be indexed.

---

## Difference: Entity Values vs Import Values

The library has TWO sets of value objects:

### Entity Values (`src/Entity/Value/`)
- Used in actual ElasticSearch entities
- Extend entity value objects
- For runtime application use
- Examples: `Entity\Value\StringValue`, `Entity\Value\IntegerValue`

### Import Values (`src/Entity/Import/`)
- Used ONLY during import process
- Simpler, focused on data transformation
- Include special types like `NoValue`, `EmptyValue`
- Used with `AbstractImport::toArray()`

**Key Difference:**

```php
// Entity Value - for entities
class Product extends AbstractElasticEntity {
    public function __construct(
        private MyCustomStringValue $name // Extends Entity\ValueInterface
    ) {}
}

// Import Value - for import preparation
class ProductImport extends AbstractImport {
    public function __construct(
        private \Spameri\Elastic\Entity\Import\StringValue $name
    ) {}
}
```

Import values are simpler wrappers, entity values can have validation logic.

---

## Exception Handling

The import system uses three exception types for flow control:

### Omit Exception

Skips the current item and continues import.

```php
if (empty($data['required_field'])) {
    throw new \Spameri\Elastic\Import\Exception\Omit('Missing required field');
}
```

**Use Case:** Invalid/incomplete data that can be safely skipped.

**Behavior:**
- Logged as omitted
- Import continues with next item
- Does not affect other items

---

### Error Exception

Logs an error but continues import.

```php
if ($price < 0) {
    throw new \Spameri\Elastic\Import\Exception\Error('Invalid price: negative value');
}
```

**Use Case:** Data issues that should be logged but don't stop the import.

**Behavior:**
- Logged as error
- Import continues with next item
- Useful for generating error reports

---

### Fatal Exception

Stops the entire import immediately.

```php
if (!$requiredService->isAvailable()) {
    throw new \Spameri\Elastic\Import\Exception\Fatal('Service unavailable');
}
```

**Use Case:** Critical failures where continuing would cause data corruption.

**Behavior:**
- Logged as fatal
- Import stops immediately
- Lock is released
- Remaining items are not processed

---

### AlreadyLocked Exception

Thrown when import is already running.

```php
try {
    $import->execute($options);

} catch (\Spameri\Elastic\Import\Exception\AlreadyLocked $e) {
    echo 'Import is already running';
}
```

**Behavior:**
- Prevents concurrent imports
- Check lock before running

---

## Locking System

Prevents concurrent imports that could corrupt data.

### FileLock (Production)

Uses filesystem locks. Safe for single-server deployments.

```php
$lock = new \Spameri\Elastic\Import\Lock\FileLock('/path/to/locks');
```

**Configuration in DI:**

```neon
services:
    - \Spameri\Elastic\Import\Lock\FileLock('%tempDir%/locks')
```

**Behavior:**
- Creates lock file when import starts
- Extends lock duration during import (via `$lock->extend()`)
- Automatically releases on completion
- Throws `AlreadyLocked` if lock exists

---

### NullLock (Development/Testing)

No locking. Allows concurrent imports.

```php
$lock = new \Spameri\Elastic\Import\Lock\NullLock();
```

**Use Case:** Development, testing, or single-user scenarios.

---

## Logging System

### LoggerHandler (Production)

Logs to Monolog with configurable channels.

**Configuration:**

```neon
services:
    import.logger:
        factory: \Monolog\Logger('import')
        setup:
            - pushHandler(@import.fileHandler)

    import.fileHandler:
        factory: \Monolog\Handler\StreamHandler('%logDir%/import.log')

    - \Spameri\Elastic\Import\LoggerHandler(@import.logger)
```

**Logged Information:**
- Item start (with key)
- Prepared data (before import)
- Response (after import)
- Exceptions (omit, error, fatal)

---

### NullLoggerHandler (Testing)

No logging. Silent operation.

```php
$loggerHandler = new \Spameri\Elastic\Import\NullLoggerHandler();
```

---

## Progress Tracking

### ConsoleHandler (CLI)

Displays progress bar in console.

```php
$runHandler = new \Spameri\Elastic\Import\RunHandler\ConsoleHandler($output);
```

**Features:**
- Real-time progress bar
- Item count
- Percentage complete
- Estimated time remaining

**Output Example:**
```
Importing products...
 1000/5000 [======>-----] 20% 2 mins
```

---

### NullHandler (Background)

No output. For background jobs.

```php
$runHandler = new \Spameri\Elastic\Import\RunHandler\NullHandler();
```

---

## Post-Import Hooks

Implement `AfterImportInterface` for post-processing.

### Example: Update Database

```php
class UpdateProductTimestamp implements \Spameri\Elastic\Import\AfterImportInterface
{
    public function __construct(
        private \Nette\Database\Explorer $database
    ) {}

    /**
     * @param \Nette\Database\Table\ActiveRow $item
     */
    public function process(
        $item,
        \Spameri\Elastic\Import\ResponseInterface $response
    ): void
    {
        $this->database
            ->table('products')
            ->where('id', $item['id'])
            ->update([
                'last_indexed' => new \DateTime(),
                'elastic_id' => $response->id(),
            ]);
    }
}
```

---

### Example: Send Notification

```php
class ImportCompleteNotification implements \Spameri\Elastic\Import\AfterImportInterface
{
    private int $count = 0;

    public function process(
        $item,
        \Spameri\Elastic\Import\ResponseInterface $response
    ): void
    {
        $this->count++;

        if ($this->count % 1000 === 0) {
            // Send notification every 1000 items
            $this->notificationService->send(
                "Imported {$this->count} items so far"
            );
        }
    }
}
```

---

### NullAfterImport

No post-processing.

```php
$afterImport = new \Spameri\Elastic\Import\AfterImport\NullAfterImport();
```

---

## Run vs SimpleRun

### Run

Full-featured import orchestrator with:
- Progress bar initialization
- Logging setup with date-based directories
- Lock extension during execution
- Comprehensive error handling

**Use when:** You need full import features.

---

### SimpleRun

Lightweight version without:
- Automatic progress bar setup
- Automatic logger setup
- Some convenience methods

**Use when:** You want to customize the import flow.

---

## Run\Options

Configuration for import execution.

### Constructor

```php
public function __construct(
    private int $lockDuration
)
```

### Properties

- `$lockDuration` - Lock timeout in seconds (default: 3600)

### Usage

```php
$options = new \Spameri\Elastic\Import\Run\Options(
    lockDuration: 7200 // 2 hours
);
```

**Note:** Lock is automatically extended during import via `$lock->extend()` in the Run loop.

---

## Complete Working Example

### 1. Entity

```php
<?php

namespace App\Entity;

class Product extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $name,
        private float $price,
        private int $stock,
        private array $tags,
    ) {
        $this->id = $id;
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}
```

---

### 2. Import Structure

```php
<?php

namespace App\Import;

// Data Provider
class ProductDataProvider implements \Spameri\Elastic\Import\DataProviderInterface
{
    public function __construct(
        private \Nette\Database\Explorer $database
    ) {}

    public function count(\Spameri\Elastic\Import\Run\Options $options): int
    {
        return $this->database->table('products')->count();
    }

    public function provide(\Spameri\Elastic\Import\Run\Options $options): \Generator
    {
        $offset = 0;
        $limit = 100;

        while (true) {
            $items = $this->database
                ->table('products')
                ->limit($limit, $offset)
                ->fetchAll();

            if (!count($items)) break;

            yield from $items;
            $offset += $limit;
        }
    }
}

// Import Data Class
class ProductImport extends \Spameri\Elastic\Entity\AbstractImport
{
    public function __construct(
        int $key,
        private \Spameri\Elastic\Entity\Import\StringValue $name,
        private \Spameri\Elastic\Entity\Import\FloatValue $price,
        private \Spameri\Elastic\Entity\Import\IntegerValue $stock,
        private \Spameri\Elastic\Entity\Import\ArrayValue $tags,
    ) {
        parent::__construct($key);
    }
}

// Prepare Data
class PrepareProductData implements \Spameri\Elastic\Import\PrepareImportDataInterface
{
    public function prepare($entityData): \Spameri\Elastic\Entity\AbstractImport
    {
        if (empty($entityData['name'])) {
            throw new \Spameri\Elastic\Import\Exception\Omit('Empty name');
        }

        return new ProductImport(
            key: $entityData['id'],
            name: new \Spameri\Elastic\Entity\Import\StringValue(
                $entityData['name'],
                'name'
            ),
            price: new \Spameri\Elastic\Entity\Import\FloatValue(
                $entityData['price'],
                'price'
            ),
            stock: new \Spameri\Elastic\Entity\Import\IntegerValue(
                $entityData['stock'],
                'stock'
            ),
            tags: new \Spameri\Elastic\Entity\Import\ArrayValue(
                explode(',', $entityData['tags'] ?? ''),
                'tags'
            ),
        );
    }
}

// Data Import
class ProductDataImport implements \Spameri\Elastic\Import\DataImportInterface
{
    public function __construct(
        private \App\Model\ProductService $productService
    ) {}

    public function import(
        \Spameri\Elastic\Entity\AbstractImport $entity
    ): \Spameri\Elastic\Import\ResponseInterface
    {
        $data = $entity->toArray();

        $product = new \App\Entity\Product(
            new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
            $data['name'],
            $data['price'],
            $data['stock'],
            $data['tags'],
        );

        $id = $this->productService->insert($product);

        return new \Spameri\Elastic\Import\Response\SimpleResponse(
            $id,
            $entity
        );
    }
}

// Run Class
class ProductImportRun extends \Spameri\Elastic\Import\Run
{
    // Wire all dependencies in constructor
}
```

---

### 3. Neon Configuration

```neon
services:
    # Lock
    import.lock: \Spameri\Elastic\Import\Lock\FileLock('%tempDir%/locks')

    # Logger
    import.logger:
        factory: \Monolog\Logger('import')
        setup:
            - pushHandler(@import.fileHandler)

    import.fileHandler:
        factory: \Monolog\Handler\StreamHandler('%logDir%/import.log')

    import.loggerHandler: \Spameri\Elastic\Import\LoggerHandler(@import.logger)

    # Run Handler
    import.runHandler: \Spameri\Elastic\Import\RunHandler\NullHandler()

    # After Import
    import.afterImport: \Spameri\Elastic\Import\AfterImport\NullAfterImport()

    # Import Components
    import.dataProvider: App\Import\ProductDataProvider
    import.prepareData: App\Import\PrepareProductData
    import.dataImport: App\Import\ProductDataImport

    # Import Run
    import.productRun: App\Import\ProductImportRun(
        '%logDir%/import'
        @import.loggerHandler
        @import.lock
        @import.runHandler
        @import.dataProvider
        @import.prepareData
        @import.dataImport
        @import.afterImport
    )
```

---

### 4. Execute

```php
// In console command or cron
$productImportRun = $container->getByType(App\Import\ProductImportRun::class);

$options = new \Spameri\Elastic\Import\Run\Options(3600);

try {
    $productImportRun->execute($options);

} catch (\Spameri\Elastic\Import\Exception\AlreadyLocked $e) {
    echo 'Import already running';

} catch (\Throwable $e) {
    \Tracy\Debugger::log($e);
    echo 'Import failed: ' . $e->getMessage();
}
```

---

## Best Practices

### 1. Use Generators for Large Datasets

Always yield items instead of loading all into memory:

```php
// Good
public function provide(\Spameri\Elastic\Import\Run\Options $options): \Generator
{
    $offset = 0;
    while ($items = $this->fetchBatch($offset, 100)) {
        yield from $items;
        $offset += 100;
    }
}

// Bad - loads everything into memory
public function provide(\Spameri\Elastic\Import\Run\Options $options): array
{
    return $this->database->fetchAll(); // 1M records = memory exhaustion
}
```

---

### 2. Set Appropriate Lock Duration

Lock should be longer than expected import time:

```php
// For 10k items taking ~30 minutes
$options = new \Spameri\Elastic\Import\Run\Options(3600); // 1 hour

// For 1M items taking ~5 hours
$options = new \Spameri\Elastic\Import\Run\Options(21600); // 6 hours
```

Lock is automatically extended during import, but initial duration prevents stale locks.

---

### 3. Use Correct Exception Types

```php
// Omit - skip this item
if (empty($requiredField)) {
    throw new \Spameri\Elastic\Import\Exception\Omit('Missing required field');
}

// Error - log but continue
if ($value < 0) {
    throw new \Spameri\Elastic\Import\Exception\Error('Negative value');
}

// Fatal - stop everything
if (!$criticalService->isAvailable()) {
    throw new \Spameri\Elastic\Import\Exception\Fatal('Service down');
}
```

---

### 4. Validate in PrepareImportData

Do all validation in `PrepareImportData`, not in `DataImport`:

```php
// Good
class PrepareProductData implements PrepareImportDataInterface
{
    public function prepare($data): AbstractImport
    {
        // Validate here
        if (strlen($data['name']) > 255) {
            throw new Exception\Omit('Name too long');
        }

        return new ProductImport(/* validated data */);
    }
}

// Bad - validation in wrong place
class ProductDataImport implements DataImportInterface
{
    public function import(AbstractImport $entity): ResponseInterface
    {
        // Too late to validate - data already prepared
    }
}
```

---

### 5. Use FileLock in Production

Always use `FileLock` in production to prevent data corruption:

```php
// Production
services:
    import.lock: \Spameri\Elastic\Import\Lock\FileLock('%tempDir%/locks')

// Development only
services:
    import.lock: \Spameri\Elastic\Import\Lock\NullLock()
```

---

## Troubleshooting

### Import Won't Start - AlreadyLocked

**Problem:** Previous import didn't release lock (crashed, killed, etc.)

**Solution:** Manually delete lock file:

```bash
rm /path/to/locks/ProductImportRun.lock
```

Or increase lock duration if import legitimately takes longer.

---

### Memory Exhaustion

**Problem:** Import runs out of memory

**Solutions:**
1. Use generators (yield) instead of arrays
2. Reduce batch size in data provider
3. Increase PHP memory_limit
4. Use Scroll API for reading (if applicable)

---

### Slow Import

**Problem:** Import takes too long

**Solutions:**
1. Use `InsertMultiple` instead of single `Insert`
2. Increase batch size (more items per query)
3. Disable index refresh during import, refresh once at end
4. Use bulk API directly (advanced)

---

### Items Skipped Without Errors

**Problem:** Items silently skipped

**Cause:** `Omit` exceptions are caught and logged

**Solution:** Check logs for omitted items:

```bash
tail -f /path/to/logs/import.log | grep "Omit"
```

---

## See Also

- [Fill Data](06_fill_data.md) - Manual entity creation
- [Model Services](14_model_services.md) - InsertMultiple and bulk operations
- [Entity Class](03_entity_class.md) - Entity structure
- [Quick Start](00_quick_start.md) - Complete import example
