<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Wthealth\JsonSchemaRequest\Console\MakeJsonSchemaRequestCommand;

class JsonSchemaRequestServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->bind('command.make:json-request', MakeJsonSchemaRequestCommand::class);
        $this->commands(['command.make:json-request']);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->resolving(JsonSchemaRequest::class, function ($request, $app) {
            $request = JsonSchemaRequest::createFrom($app['request'], $request);
            $request->setContainer($app);
        });
    }
}
