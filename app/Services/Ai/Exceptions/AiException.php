<?php

namespace App\Services\Ai\Exceptions;

class AiException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly ?string $context = null,
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function context(): ?string
    {
        return $this->context;
    }
}
