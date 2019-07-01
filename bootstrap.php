<?php

use Honeybadger\Honeybadger;
use The\Db;
use The\Model;
use function The\option;
use function The\service;

require_once 'vendor/autoload.php';

option('root_dir', __DIR__);
option('views_dir', option('root_dir') . '/views');

Model::setDb(function () {
    return option('db');
});

option('db', service(function () {
    return new Db(getenv('DATABASE_URL'));
}));

option('honeybadger', service(function() {
    return Honeybadger::new([
        'api_key'          => getenv('HONEYBADGER_API_KEY') ?: null,
        'environment_name' => getenv('APP_ENV') ?: 'unknown',
        'handlers'         => ['exception' => false, 'error' => false],
    ]);
}));
