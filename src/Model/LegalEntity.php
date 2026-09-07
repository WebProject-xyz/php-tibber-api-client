<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class LegalEntity
{
    public function __construct(
        public ?string $id = null,
        public ?string $firstName = null,
        public ?bool $isCompany = null,
        public ?string $name = null,
        public ?string $middleName = null,
        public ?string $lastName = null,
        public ?string $organizationNo = null,
        public ?string $language = null,
        public ?ContactInfo $contactInfo = null,
    ) {
    }
}
