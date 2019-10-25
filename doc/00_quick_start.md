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

### II. Configure

Now you need to tell library where is ElasticSearch running. Default values are **localhost**
and port **9200**. That means if you are running ElasticSearch locally with default port, no
need to configure anything. 

```yaml
spameriElasticSearch:
	host: 192.168.0.14
	port: 9200
```

### III. 








