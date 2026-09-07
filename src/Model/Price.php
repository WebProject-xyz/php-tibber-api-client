<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

use DateTimeImmutable;
use WebProject\TibberApiClient\Model\Enum\PriceLevel;

final readonly class Price
{
    public function __construct(
        public float|int|null $total = null,
        public float|int|null $energy = null,
        public float|int|null $tax = null,
        public ?DateTimeImmutable $startsAt = null,
        public ?string $currency = null,
        public ?PriceLevel $level = null,
    ) {
    }

    public function getTotalFloat(): ?float
    {
        return null !== $this->total ? (float) $this->total : null;
    }

    public function getEnergyFloat(): ?float
    {
        return null !== $this->energy ? (float) $this->energy : null;
    }

    public function getTaxFloat(): ?float
    {
        return null !== $this->tax ? (float) $this->tax : null;
    }
}
