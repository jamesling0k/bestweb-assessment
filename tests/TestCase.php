<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if ($app['config']->get('database.default') !== 'mysql'
            || ! str_ends_with((string) $app['config']->get('database.connections.mysql.database'), '_testing')
            || $app['config']->get('database.connections.mysql.url')) {
            throw new RuntimeException('Tests require a dedicated MySQL database ending in _testing and no DB_URL.');
        }

        return $app;
    }
}
