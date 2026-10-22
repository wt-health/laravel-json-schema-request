<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Tests\Support;

class HookedRequest extends ApiRequest
{
    public bool $passedValidationCalled = false;

    protected function prepareForValidation()
    {
        $this->merge(['last_name' => 'Default']);
    }

    protected function passedValidation()
    {
        $this->passedValidationCalled = true;
    }
}
