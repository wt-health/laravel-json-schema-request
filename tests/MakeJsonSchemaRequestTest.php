<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests;

use Orchestra\Testbench\TestCase;
use Wthealth\JsonSchemaRequest\JsonSchemaRequestServiceProvider;

class MakeJsonSchemaRequestTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [JsonSchemaRequestServiceProvider::class];
    }

    public function test_it_should_create_a_request_class()
    {
        $this->artisan('make:json-request', ['name' => 'MySchemaRequest'])->assertExitCode(0);
    }
}
