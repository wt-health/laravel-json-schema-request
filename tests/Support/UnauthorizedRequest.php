<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests\Support;

class UnauthorizedRequest extends ApiRequest
{
    public function authorize()
    {
        return false;
    }
}
