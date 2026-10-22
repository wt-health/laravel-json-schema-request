<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests\Support;

class SchemaRepository
{
    private array $required;

    public function __construct(array $required = ['first_name'])
    {
        $this->required = $required;
    }

    public function user(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'first_name' => ['type' => 'string'],
                'nickname' => ['type' => 'string'],
            ],
            'required' => $this->required,
        ];
    }
}
