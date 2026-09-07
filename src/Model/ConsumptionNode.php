<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

use DateTimeImmutable;

final readonly class ConsumptionNode
{
    public function __construct(
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public float|int|null $cost = null,
        public float|int|null $unitPrice = null,
        public float|int|null $unitPriceVAT = null,
        public float|int|null $consumption = null,
        public ?string $consumptionUnit = null,
        public float|int|null $totalCost = null,
        public float|int|null $unitCost = null,
        public ?string $currency = null,
    ) {
    }

    public function getCostFloat(): ?float
    {
        return null !== $this->cost ? (float) $this->cost : null;
    }

    public function getUnitPriceFloat(): ?float
    {
        return null !== $this->unitPrice ? (float) $this->unitPrice : null;
    }

    public function getUnitPriceVATFloat(): ?float
    {
        return null !== $this->unitPriceVAT ? (float) $this->unitPriceVAT : null;
    }

    public function getConsumptionFloat(): ?float
    {
        return null !== $this->consumption ? (float) $this->consumption : null;
    }

    public function getTotalCostFloat(): ?float
    {
        return null !== $this->totalCost ? (float) $this->totalCost : null;
    }

    public function getUnitCostFloat(): ?float
    {
        return null !== $this->unitCost ? (float) $this->unitCost : null;
    }
}
