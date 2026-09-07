<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class ContactInfo
{
    public function __construct(
        public ?string $email = null,
        public ?string $mobile = null,
    ) {
    }
}
