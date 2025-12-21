# Create Index with Mapping for Entity

Entity is configured ([see entity class guide](03_entity_class.md)) and next thing is to get these settings to ElasticSearch.

## Index Configuration Class

Before creating an index, you need a mapping configuration class that implements `IndexConfigInterface`:

```php
<?php declare(strict_types = 1);

namespace App\Model\Settings;

class VideoMapping implements \Spameri\Elastic\Settings\IndexConfigInterface
{
    public function __construct(
        private readonly string $indexName = 'video',
    ) {}

    public function provide(): \Spameri\ElasticQuery\Mapping\Settings
    {
        $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

        // Add field mappings
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'title',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
            )
        );

        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'year',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_INTEGER
            )
        );

        return $settings;
    }

    public function entityClass(): array
    {
        return [\App\Model\Entity\Video::class];
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
    - App\Model\Settings\VideoMapping
```

## Index Creating

### Variant 1. - No previous data

**When to use:**
- You don't have any index
- Your ElasticSearch installation is clean with no index for your entities

**How:**

Entity is configured from previous [step](02_configuration.md) and we just need to run command.

```bash
php bin/console spameri:elastic:initialize-index
```

Can be used for specific entity:

```bash
php bin/console spameri:elastic:initialize-index video
```

### Variant 2. - Deleting previous data

**When to use:**
- You have an existing index with outdated data and old configuration
- You don't need to keep the data (usually when generating from another database)

**How:**

Add the `-f` (force) option. The command first deletes the old index, then creates a new empty index with new settings.

```bash
php bin/console spameri:elastic:initialize-index -f
```

Can be used for specific entity:

```bash
php bin/console spameri:elastic:initialize-index -f video
```

### Variant 3. - Preserving previous data

**When to use:**
- You don't have another source for data saved in ElasticSearch
- You need to change mapping but keep the data

**How:**

1. Backup your data with the dump command:
```bash
php bin/console spameri:elastic:dump-index <index-name>
```
This creates a bulk JSON document with all data from the index.

2. Delete and recreate the index with new mapping (Variant 2):
```bash
php bin/console spameri:elastic:initialize-index -f <index-name>
```

3. Load your old data back:
```bash
php bin/console spameri:elastic:load-dump <dump-file-path>
```

Optional: specify batch size for large datasets:
```bash
php bin/console spameri:elastic:load-dump <dump-file-path> --step=500
```

## Available Commands

| Command | Description |
|---------|-------------|
| `spameri:elastic:initialize-index` | Create indexes from configuration |
| `spameri:elastic:create-index <name>` | Create a single index |
| `spameri:elastic:delete-index <name>` | Delete an index |
| `spameri:elastic:dump-index <name>` | Export index data to JSON |
| `spameri:elastic:load-dump <file>` | Import data from JSON dump |
| `spameri:elastic:add-alias <index> <alias>` | Add alias to index |
| `spameri:elastic:remove-alias <index> <alias>` | Remove alias from index |

## Mapping Example

For a complete mapping example, see [Mapping Configuration](02_configuration.md).

Here's a more detailed example with nested objects:

```php
public function provide(): \Spameri\ElasticQuery\Mapping\Settings
{
    $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

    // Text field with subfields for different search scenarios
    $nameFields = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\SubFields(
        'name',
        \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
    );
    $nameFields->addMappingField(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'keyword',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
        )
    );
    $settings->addMappingSubField($nameFields);

    // Nested object
    $story = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
        'story',
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'description',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
            ),
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'tagLine',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        )
    );
    $settings->addMappingFieldObject($story);

    // Simple fields
    $settings->addMappingField(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'year',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_LONG
        )
    );

    return $settings;
}
```

## Custom Analyzers

Analyzers control how text is processed during indexing and searching. ElasticSearch's default `standard` analyzer works for basic use cases, but custom analyzers provide better search relevance for autocomplete, language-specific content, and specialized tokenization.

### EdgeNgram for Autocomplete

The `EdgeNgram` analyzer creates prefix tokens, enabling "search as you type" functionality:

```php
public function provide(): \Spameri\ElasticQuery\Mapping\Settings
{
    $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

    // Define custom EdgeNgram analyzer
    $ngramAnalyzer = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\EdgeNgram(
        minGram: 2,
        maxGram: 10,
    );
    
    // Add the EdgeNgram analyzer to index settings
    $settings->addAnalyzer(
        $ngramAnalyzer 
    );

    // Assign analyzer to a text field
    $settings->addMappingField(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'title',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
            $ngramAnalyzer,
        )
    );

    return $settings;
}
```

When you add a `CustomAnalyzerInterface` via `addAnalyzer()`, its filters (ASCII folding, lowercase, stop words, etc.) are automatically registered.

### Language-Specific Analyzers

For content in specific languages, dictionary analyzers provide stemming and stop word removal:

```php
// English content with stemming
$englishDictionary = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\EnglishDictionary();

$settings->addAnalyzer(
    $englishDictionary
);

$settings->addMappingField(
    new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
        'description',
        \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
        $englishDictionary,
    )
);
```

Available language dictionaries include: `CzechDictionary`, `EnglishDictionary`, `FrenchDictionary`, `GermanDictionary`, `SpanishDictionary`, `RussianDictionary`, and more.

### Multi-Field Configuration with SubFields

A common pattern is configuring the same field with multiple analyzers for different search scenarios:

```php
public function provide(): \Spameri\ElasticQuery\Mapping\Settings
{
    $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

    // Register analyzers first
    $edgeNgram = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\EdgeNgram(minGram: 2, maxGram: 8);
    $lowercaseAnalyzer = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\Lowercase();
    
    $settings->addAnalyzer(
        $edgeNgram
    );
    
    $settings->addAnalyzer(
        $lowercaseAnalyzer
    );

    // Main field with standard analysis
    $nameFields = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\SubFields(
        'name',
        \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
    );

    // SubField for autocomplete (prefix matching)
    $nameFields->addMappingField(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'autocomplete',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
            $edgeNgram,
        )
    );

    // SubField for exact matching (keyword)
    $nameFields->addMappingField(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'keyword',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
        )
    );

    // SubField for case-insensitive matching
    $nameFields->addMappingField(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'lowercase',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
            $lowercaseAnalyzer,
        )
    );

    $settings->addMappingSubField($nameFields);

    return $settings;
}
```

This creates a `name` field queryable as:
- `name` - standard text search
- `name.autocomplete` - prefix/typeahead search
- `name.keyword` - exact match, sorting, aggregations
- `name.lowercase` - case-insensitive exact matching

### Available Custom Analyzers

| Analyzer | Use Case |
|----------|----------|
| `EdgeNgram` | Autocomplete, prefix search |
| `WordDelimiter` | Split on case changes, delimiters |
| `Lowercase` | Case-insensitive matching with ASCII folding |
| `CommonGrams` | Bigrams of common words |
| `*Dictionary` | Language-specific stemming and stop words |

### Complete Example

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

        // Register custom analyzers
        $edgeNgram = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\EdgeNgram(minGram: 2, maxGram: 15);
        $englishDictionary = new \Spameri\ElasticQuery\Mapping\Analyzer\Custom\EnglishDictionary();
        
        $settings->addAnalyzer(
            $edgeNgram
        );
        $settings->addAnalyzer(
            $englishDictionary
        );

        // Product name with autocomplete
        $nameFields = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\SubFields(
            'name',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
        );
        $nameFields->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'autocomplete',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
                $edgeNgram,
            )
        );
        $nameFields->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'keyword',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        );
        $settings->addMappingSubField($nameFields);

        // Description with language analyzer
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'description',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
                $englishDictionary,
            )
        );

        // Simple fields
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'price',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_FLOAT
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

## Tips

- **Never use field named `id` in mapping** - it conflicts with ElasticSearch's internal `_id`. Use `databaseId` or `externalId` depending on where your data originates.
- **Keep field names unique** - don't use the same field name at different nesting levels.
- **Use aliases** - for zero-downtime reindexing, use aliases instead of direct index names.

## Next Steps

- [Fill Data](06_fill_data.md) - Creating and saving entities
- [Mapping Attributes](18_mapping_attributes.md) - Entity property annotations
- [Migrations](24_migrations.md) - Schema and data migrations
