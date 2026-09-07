<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class Address
{
    public function __construct(
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $address3 = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $latitude = null,
        public ?string $longitude = null,
    ) {
    }

    public function getLatitudeFloat(): ?float
    {
        return null !== $this->latitude && is_numeric($this->latitude) ? (float) $this->latitude : null;
    }

    public function getLongitudeFloat(): ?float
    {
        return null !== $this->longitude && is_numeric($this->longitude) ? (float) $this->longitude : null;
    }
}
