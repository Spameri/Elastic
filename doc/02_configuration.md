# Configuration

## Extension Registration

In your config neon file, register the extension:

```neon
extensions:
    spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension
```

## Configuration Options

Configure your ElasticSearch connection and library options:

```neon
spameriElasticSearch:
    host: 127.0.0.1
    port: 9200
    debug: true
    version: 8
    synonymPath: %appDir%/config/synonyms.txt
```

### Available Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `host` | string | `localhost` | ElasticSearch server hostname or IP address |
| `port` | int | `9200` | ElasticSearch server port |
| `debug` | bool | `false` | Enable Tracy debug bar panel for query inspection |
| `version` | int | `8` | ElasticSearch version (7 or 8) |
| `synonymPath` | string\|null | `null` | Path to synonyms file for text analysis |

## Debug Mode

When `debug: true` is set:
- Tracy debug bar panel is enabled
- All queries are logged with timing information
- Query details can be inspected in the debug panel

```neon
spameriElasticSearch:
    debug: %debugMode%  # Use Nette's debug mode
```

See [Debugging Guide](16_debugging.md) for more details on the Tracy panel.

## Entity and Index Registration

Entities are registered through `IndexConfigInterface` implementations. Each implementation:
1. Defines the index name via `indexName()`
2. Specifies which entity classes it manages via `entityClass()`
3. Provides mapping configuration via `provide()`

Register your mapping class as a DI service:

```neon
services:
    - App\Model\Settings\VideoMapping
    - App\Model\Settings\PersonMapping
```

The system auto-discovers all `IndexConfigInterface` implementations via `container->findByType()`.

See [Index Mapping](05_new_index_with_mapping.md) for detailed mapping configuration.

## Symfony Console Integration

For index management commands, you need a Symfony Console implementation. With Kdyby/Console:

```neon
extensions:
    console: Kdyby\Console\DI\ConsoleExtension
    spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension
```

Or with Contributte/Console:

```neon
extensions:
    console: Contributte\Console\DI\ConsoleExtension
    spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension
```

## Version Compatibility

The `version` option affects query generation and response parsing:

| Version | ElasticSearch | Notes                   |
|---------|---------------|-------------------------|
| `7`     | 7.x           | Legacy may not work     |
| `8`     | 8.x           | Legacy, partial support |
| `9`     | 9.x           | Default, recommended    |

## Complete Example

```neon
extensions:
    console: Contributte\Console\DI\ConsoleExtension
    spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension

spameriElasticSearch:
    host: %elastic.host%
    port: %elastic.port%
    debug: %debugMode%
    version: 8

services:
    - App\Model\Settings\VideoMapping
    - App\Model\Settings\PersonMapping
```

With parameters:

```neon
parameters:
    elastic:
        host: 127.0.0.1
        port: 9200
```

## Next Steps

- [Create Entity Class](03_entity_class.md)
- [Create Index Mapping](05_new_index_with_mapping.md)
