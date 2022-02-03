<?php

declare(strict_types=1);

namespace Wthealth\JsonSchemaRequest\Exceptions;

use Illuminate\Http\Response;
use Wthealth\JsonSchemaRequest\Validation\JsonSchemaValidator;

class ValidationException extends \Exception
{
    public JsonSchemaValidator $validator;

    public int $status = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function __construct(JsonSchemaValidator $validator)
    {
        parent::__construct('The given data was invalid.');
        $this->validator = $validator;
    }

    public function report()
    {
        // Do not report this exception.
    }

    public function render()
    {
        return response()->json(['errors' => $this->validator->errors()], $this->status);
    }
}
