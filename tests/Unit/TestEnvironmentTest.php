<?php

namespace Tests\Unit;

use Illuminate\Support\Env;
use PHPUnit\Framework\TestCase;

class TestEnvironmentTest extends TestCase
{
    public function test_laravel_reads_the_isolated_test_environment_inside_docker(): void
    {
        $this->assertSame('testing', Env::get('APP_ENV'));
        $this->assertSame('mysql', Env::get('DB_CONNECTION'));
        $this->assertSame('bestweb_testing', Env::get('DB_DATABASE'));
        $this->assertSame('array', Env::get('SESSION_DRIVER'));
        $this->assertSame('array', Env::get('CACHE_STORE'));
    }
}
