<?php declare(strict_types = 1);

/**
 * Bootstrap for unit tests that don't require ElasticSearch connection.
 */

if (\defined('__PHPSTAN_RUNNING__')) {
	return;
}

$loader = include __DIR__ . '/../vendor/autoload.php';
if ( ! $loader) {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

// configure environment
\Tester\Environment::setup();
\date_default_timezone_set('Europe/Prague');

\define('TEMP_DIR', __DIR__ . '/tmp/' . (isset($_SERVER['argv']) ? \md5(\serialize($_SERVER['argv'])) : \getmypid()));

Tester\Helpers::purge(\TEMP_DIR);
Tracy\Debugger::$logDirectory = \TEMP_DIR;

return $loader;
