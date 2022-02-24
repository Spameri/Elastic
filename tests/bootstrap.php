<?php declare(strict_types = 1);

/**
 * This file is part of the Spameri (http://www.github.com/spameri)
 *
 * Copyright (c) 2018 Václav Čevela (vcevela@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this
 * source code.
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

$ch = \curl_init();
\curl_setopt($ch, \CURLOPT_URL, \SpameriTests\Elastic\Config::CONNECTION . '/' . \SpameriTests\Elastic\Config::INDEX . '*');
\curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
\curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'DELETE');
\curl_setopt($ch, \CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

\curl_exec($ch);

\curl_close($ch);

return $loader;
