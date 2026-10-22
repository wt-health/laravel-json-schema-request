<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests;

use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase;
use Wthealth\JsonSchemaRequest\JsonSchemaRequestServiceProvider;
use Wthealth\JsonSchemaRequest\Tests\Support\ApiRequest;
use Wthealth\JsonSchemaRequest\Tests\Support\HookedRequest;
use Wthealth\JsonSchemaRequest\Tests\Support\InjectedSchemaRequest;
use Wthealth\JsonSchemaRequest\Tests\Support\SchemaRepository;
use Wthealth\JsonSchemaRequest\Tests\Support\UnauthorizedRequest;

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

    public function test_it_should_not_resolve_the_validator_more_than_once()
    {
        $resolved = null;
        $router = $this->app->get(Router::class);
        $router->post('/test', function (ApiRequest $request) use (&$resolved) {
            $resolved = $request;

            return response()->noContent();
        });

        $this->postJson('/test', [
            'first_name' => 'foo',
            'last_name' => 'bar',
            'email' => 'foo@bar.com',
        ])->assertNoContent();

        $this->assertSame($resolved->getValidatorInstance(), $resolved->getValidatorInstance());
    }

    public function test_schema_dependencies_are_injected_from_the_container()
    {
        $this->app->instance(SchemaRepository::class, new SchemaRepository(['nickname']));

        $router = $this->app->get(Router::class);
        $router->post('/test', fn (InjectedSchemaRequest $request) => $request->validated());

        $this->postJson('/test', ['first_name' => 'foo'])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'nickname' => ['The property nickname is required'],
                ],
            ]);

        $this->postJson('/test', ['nickname' => 'foo'])->assertOk()->assertJson(['nickname' => 'foo']);
    }

    public function test_it_should_reject_unauthorized_requests_before_validating()
    {
        $router = $this->app->get(Router::class);
        $router->post('/test', fn (UnauthorizedRequest $request) => response(200));

        $this->postJson('/test', ['first_name' => 'foo'])->assertForbidden();
    }

    public function test_it_should_run_the_validation_lifecycle_hooks()
    {
        $router = $this->app->get(Router::class);
        $router->post('/test', fn (HookedRequest $request) => [
            'validated' => $request->validated(),
            'passed_validation_called' => $request->passedValidationCalled,
        ]);

        $this->postJson('/test', [
            'first_name' => 'foo',
            'email' => 'foo@bar.com',
        ])->assertOk()->assertExactJson([
            'validated' => [
                'first_name' => 'foo',
                'last_name' => 'Default',
                'email' => 'foo@bar.com',
            ],
            'passed_validation_called' => true,
        ]);
    }
}
