<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
$tmpPath = '/tmp';

$_ENV['APP_PACKAGES_CACHE'] = $tmpPath . '/packages.php';
$_ENV['APP_SERVICES_CACHE'] = $tmpPath . '/services.php';
$_ENV['APP_ROUTES_CACHE'] = $tmpPath . '/routes-v7.php';
$_ENV['APP_EVENTS_CACHE'] = $tmpPath . '/events.php';
$_ENV['APP_CONFIG_CACHE'] = $tmpPath . '/config.php';

putenv('APP_PACKAGES_CACHE=' . $_ENV['APP_PACKAGES_CACHE']);
putenv('APP_SERVICES_CACHE=' . $_ENV['APP_SERVICES_CACHE']);
putenv('APP_ROUTES_CACHE=' . $_ENV['APP_ROUTES_CACHE']);
putenv('APP_EVENTS_CACHE=' . $_ENV['APP_EVENTS_CACHE']);
putenv('APP_CONFIG_CACHE=' . $_ENV['APP_CONFIG_CACHE']);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');

$app->handleRequest(Request::capture());