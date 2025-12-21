# Intro

## Install

Use composer: `composer require spameri/elastic`

## Usage

### 1. Configure ElasticSearch

In your config neon, enable the extension:

```neon
extensions:
    spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension
```

Optionally add a Symfony Console implementation:

```neon
extensions:
    console: Contributte\Console\DI\ConsoleExtension
```

Then configure your ElasticSearch connection:

```neon
spameriElasticSearch:
    host: 127.0.0.1
    port: 9200
```

For more config options see [Configuration Guide](02_configuration.md).

#### Raw Client Usage

After configuration you can use ElasticSearch directly:

```php
$result = $this->clientProvider->client()->search(
    (
        new \Spameri\ElasticQuery\Document(
            $index,
            new \Spameri\ElasticQuery\Document\Body\Plain(
                $elasticQuery->toArray()
            )
        )
    )->toArray()
);
```

The client is provided by **elasticsearch/elasticsearch**. See their [documentation](https://github.com/elastic/elasticsearch-php#quickstart).

For type-safe queries use [Spameri/ElasticQuery](https://github.com/Spameri/ElasticQuery/blob/master/doc/02-query-objects.md).

---

### 2. First Entity

#### [Create entity class](03_entity_class.md)

#### [Create entity factory](11_entity_factory.md)

---

### 3. Mapping

#### [Create new index with mapping](05_new_index_with_mapping.md)

---

### 4. Fill with Data

#### [Create and save entity](06_fill_data.md)

#### [Saving process explained](07_save_explained.md)

---

### 5. Get Data from ElasticSearch

#### [Get data by ID](08_basic_get.md)

#### [Get data by tag](13_advanced_get.md)

---

### 6. Filter Data from ElasticSearch

#### [Match data](09_match_get.md)

---

### 7. Aggregate Data from ElasticSearch

#### [Aggregate data](10_aggregate.md)

---

### 8. Advanced Topics

#### Core Concepts

- [Data interfaces](04_data_interfaces.md) - Entity and collection interfaces
- [Value Objects](22_value_objects.md) - Type-safe value objects
- [Identity Map](21_identity_map.md) - Entity caching and reference integrity
- [STI Guide](20_sti_guide.md) - Single Table Inheritance

#### Reference Documentation

- [EntityManager](17_entity_manager.md) - Central API for entity operations
- [Model Services](14_model_services.md) - Low-level service API
- [Mapping Attributes](18_mapping_attributes.md) - Property annotations
- [Event System](19_event_system.md) - Lifecycle events and listeners

#### Operations

- [Console Commands](23_console_commands.md) - CLI command reference
- [Import System](15_import_system.md) - Bulk data imports
- [Migrations](24_migrations.md) - Index and data migrations
- [Debugging](16_debugging.md) - Tracy panel and troubleshooting

---

## Quick Reference

| Task | Documentation |
|------|--------------|
| Configure the library | [02_configuration.md](02_configuration.md) |
| Create an entity | [03_entity_class.md](03_entity_class.md) |
| Set up index mapping | [05_new_index_with_mapping.md](05_new_index_with_mapping.md) |
| Save entities | [06_fill_data.md](06_fill_data.md) |
| Query by ID | [08_basic_get.md](08_basic_get.md) |
| Search with filters | [13_advanced_get.md](13_advanced_get.md) |
| Use aggregations | [10_aggregate.md](10_aggregate.md) |
| Handle events | [19_event_system.md](19_event_system.md) |
| Bulk import | [15_import_system.md](15_import_system.md) |
| Debug queries | [16_debugging.md](16_debugging.md) |
| Migrate indexes | [24_migrations.md](24_migrations.md) |
| CLI commands | [23_console_commands.md](23_console_commands.md) |

---

## Document Index

| # | Document | Description |
|---|----------|-------------|
| 00 | [Quick Start](00_quick_start.md) | Step-by-step tutorial |
| 01 | [Intro](01_intro.md) | This document |
| 02 | [Configuration](02_configuration.md) | DI extension setup |
| 03 | [Entity Class](03_entity_class.md) | Creating entities |
| 04 | [Data Interfaces](04_data_interfaces.md) | Interface reference |
| 05 | [Index with Mapping](05_new_index_with_mapping.md) | Index management |
| 06 | [Fill Data](06_fill_data.md) | Saving entities |
| 07 | [Save Explained](07_save_explained.md) | Persistence internals |
| 08 | [Basic Get](08_basic_get.md) | Get by ID |
| 09 | [Match Get](09_match_get.md) | Text search |
| 10 | [Aggregate](10_aggregate.md) | Aggregations |
| 11 | [Entity Factory](11_entity_factory.md) | Custom factories |
| 13 | [Advanced Get](13_advanced_get.md) | Complex queries |
| 14 | [Model Services](14_model_services.md) | Low-level API |
| 15 | [Import System](15_import_system.md) | Bulk imports |
| 16 | [Debugging](16_debugging.md) | Tracy panel |
| 17 | [EntityManager](17_entity_manager.md) | Central API |
| 18 | [Mapping Attributes](18_mapping_attributes.md) | Property annotations |
| 19 | [Event System](19_event_system.md) | Lifecycle events |
| 20 | [STI Guide](20_sti_guide.md) | Single Table Inheritance |
| 21 | [Identity Map](21_identity_map.md) | Entity caching |
| 22 | [Value Objects](22_value_objects.md) | Type-safe values |
| 23 | [Console Commands](23_console_commands.md) | CLI command reference |
| 24 | [Migrations](24_migrations.md) | Index and data migrations |
