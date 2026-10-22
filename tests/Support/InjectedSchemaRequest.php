<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests\Support;

use Wthealth\JsonSchemaRequest\JsonSchemaRequest;

class InjectedSchemaRequest extends JsonSchemaRequest
{
    public function schema(SchemaRepository $schemas)
    {
        return $schemas->user();
    }
}
