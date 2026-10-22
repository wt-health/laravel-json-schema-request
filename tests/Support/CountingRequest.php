<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests\Support;

class CountingRequest extends ApiRequest
{
    public int $schemaCalls = 0;

    public function schema()
    {
        ++$this->schemaCalls;

        return parent::schema();
    }
}
