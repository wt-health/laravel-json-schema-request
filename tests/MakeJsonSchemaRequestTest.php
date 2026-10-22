<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
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
        $path = $this->app->path('Http/Requests/MySchemaRequest.php');
        $files = $this->app->get(Filesystem::class);
        $files->delete($path);
        $this->beforeApplicationDestroyed(fn () => $files->delete($path));

        $this->artisan('make:json-request', ['name' => 'MySchemaRequest'])->assertExitCode(0);

        $this->assertFileExists($path);

        $contents = $files->get($path);
        $this->assertStringContainsString('namespace App\Http\Requests;', $contents);
        $this->assertStringContainsString('use Wthealth\JsonSchemaRequest\JsonSchemaRequest;', $contents);
        $this->assertStringContainsString('class MySchemaRequest extends JsonSchemaRequest', $contents);

        $lint = Process::run([PHP_BINARY, '-l', $path]);
        $this->assertTrue($lint->successful(), $lint->output() . $lint->errorOutput());
    }
}
