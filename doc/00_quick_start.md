# Quick Start

## 1. Install

Use composer to install this library.
```bash
composer require spameri/elastic
```

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












