<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests\Support;

use Wthealth\JsonSchemaRequest\JsonSchemaRequest;

class ApiRequest extends JsonSchemaRequest
{
    public function schema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'first_name' => ['type' => 'string'],
                'last_name' => ['type' => 'string'],
                'email' => ['type' => 'string', 'format' => 'email'],
            ],
            'required' => ['first_name', 'last_name', 'email'],
            'additionalProperties' => false,
        ];
    }
}
