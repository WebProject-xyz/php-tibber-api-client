<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Exception;

use RuntimeException;
use Throwable;

class TibberApiException extends RuntimeException implements TibberExceptionInterface
{
    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        private readonly array $errors = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
