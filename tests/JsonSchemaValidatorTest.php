<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests;

use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Wthealth\JsonSchemaRequest\Exceptions\ValidationException;
use Wthealth\JsonSchemaRequest\Validation\JsonSchemaValidator;

class JsonSchemaValidatorTest extends TestCase
{
    protected array $schema = [
        'type' => 'object',
        'properties' => [
            'first_name' => ['type' => 'string'],
            'last_name' => ['type' => 'string'],
            'email' => ['type' => 'string', 'format' => 'email'],
        ],
        'required' => ['first_name', 'last_name'],
        'additionalProperties' => false,
    ];

    protected array $validPayload = [
        'first_name' => 'Joe',
        'last_name' => 'Bloggs',
        'email' => 'foo@bar.com',
    ];

    public function test_it_should_throw_a_http_exception_on_failure()
    {
        $validator = new JsonSchemaValidator(new Validator(), $this->schema, [
            'first_name' => 'Joe',
            'email' => 'my-email',
        ]);

        $exception = null;

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            // If this is caught $exception will be assigned.
        }

        $this->assertNotNull($exception);
    }

    public function test_it_should_accept_valid_data()
    {
        $validator = new JsonSchemaValidator(new Validator(), $this->schema, $this->validPayload);
        $this->assertSame($this->validPayload, $validator->validate());
        $this->assertEmpty($validator->errors());
    }

    public function test_it_supports_after_validation_hooks()
    {
        $validator = new JsonSchemaValidator(new Validator(), $this->schema, $this->validPayload);

        $called = false;
        $validator->after(function () use (&$called) {
            $called = true;
        });

        $validator->validate();
        $this->assertTrue($called);
    }

    public function test_calling_validated_should_perform_validation_if_not_already_done()
    {
        $validator = new JsonSchemaValidator(new Validator(), $this->schema, []);

        $this->expectException(ValidationException::class);
        $validator->validated();
    }

    public function test_sometimes_is_a_no_op()
    {
        $validator = new JsonSchemaValidator(new Validator(), $this->schema, $this->validPayload);

        $neverCalled = true;

        $validator->sometimes('foo', 'bar', function () use (&$neverCalled) {
            $neverCalled = false;
        });

        $validator->validated();
        $this->assertTrue($neverCalled);
    }

    public function test_it_should_return_a_list_of_failed_constraints()
    {
        $validator = new JsonSchemaValidator(new Validator(), $this->schema, [
            'first_name' => null,
            'email' => 'foo',
            'testing' => 123,
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
        }

        $this->assertSame([
            'last_name' => ['required'],
            'first_name' => ['type'],
            'email' => ['emailFormat'],
            '' => ['additionalProp'],
        ], $validator->failed());
    }

    public function test_it_should_key_nested_errors_by_property_path()
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'address' => [
                    'type' => 'object',
                    'properties' => [
                        'street' => ['type' => 'string'],
                        'postcode' => ['type' => 'string'],
                    ],
                    'required' => ['street'],
                ],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'contacts' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => ['type' => 'string', 'format' => 'email'],
                        ],
                        'required' => ['email'],
                    ],
                ],
            ],
        ];

        $validator = new JsonSchemaValidator(new Validator(), $schema, [
            'address' => ['postcode' => 123],
            'tags' => ['a', 2],
            'contacts' => [['email' => 'foo@bar.com'], ['email' => 'foo'], []],
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame([
            'address.street' => ['The property street is required'],
            'address.postcode' => ['Integer value found, but a string is required'],
            'tags[1]' => ['Integer value found, but a string is required'],
            'contacts[1].email' => ['Invalid email'],
            'contacts[2].email' => ['The property email is required'],
        ], $validator->errors()->toArray());

        $this->assertSame([
            'address.street' => ['required'],
            'address.postcode' => ['type'],
            'tags[1]' => ['type'],
            'contacts[1].email' => ['emailFormat'],
            'contacts[2].email' => ['required'],
        ], $validator->failed());
    }
}
