<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests;

use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase;
use Wthealth\JsonSchemaRequest\JsonSchemaRequestServiceProvider;
use Wthealth\JsonSchemaRequest\Tests\Support\ApiRequest;

class JsonSchemaRequestTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [JsonSchemaRequestServiceProvider::class];
    }

    public function test_controllers_should_reject_invalid_json_resolve()
    {
        $router = $this->app->get(Router::class);
        $router->post('/test', fn (ApiRequest $request) => response(200));

        $response = $this->postJson('/test', [
            'first_name' => 'foo',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'last_name' => ['The property last_name is required'],
                    'email' => ['The property email is required'],
                ],
            ]);
    }

    public function test_it_should_accept_valid_json()
    {
        $router = $this->app->get(Router::class);
        $router->post('/test', fn (ApiRequest $request) => $request->validated());

        $data = [
            'first_name' => 'foo',
            'last_name' => 'bar',
            'email' => 'foo@bar.com',
        ];

        $this->postJson('/test', $data)->assertOk()->assertJson($data);
    }

    public function it_should_not_resolve_the_validator_more_than_once()
    {
        $request = app(ApiRequest::class);
        $request->getValidatorInstance();
    }
}
