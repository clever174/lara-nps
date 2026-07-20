<?php

namespace App\Services\Ai\Exceptions;

class AiException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly ?string $truncatedBody = null,
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Deliberately not named context() -- Laravel's exception handler calls
     * ->context() on any Throwable that defines it and expects an array
     * (Illuminate\Foundation\Exceptions\Handler::exceptionContext(), merged
     * via array_merge() in buildExceptionContext()). A string return type
     * there crashes exception reporting with a TypeError.
     */
    public function truncatedBody(): ?string
    {
        return $this->truncatedBody;
    }
}
