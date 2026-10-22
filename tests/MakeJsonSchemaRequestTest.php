<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase;
use Wthealth\JsonSchemaRequest\JsonSchemaRequest;
use Wthealth\JsonSchemaRequest\JsonSchemaRequestServiceProvider;

class MakeJsonSchemaRequestTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [JsonSchemaRequestServiceProvider::class];
    }

    public function test_it_should_create_a_request_class()
    {
        $path = $this->app->path('Http/Requests/MySchemaRequest.php');
        $files = $this->app->get(Filesystem::class);
        $files->delete($path);
        $this->beforeApplicationDestroyed(fn () => $files->delete($path));

        $this->artisan('make:json-request', ['name' => 'MySchemaRequest'])->assertExitCode(0);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Http\Requests;', $files->get($path));

        require $path;

        $this->assertTrue(is_subclass_of('App\Http\Requests\MySchemaRequest', JsonSchemaRequest::class));
    }
}
