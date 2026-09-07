<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class MeteringPointData
{
    public function __construct(
        public ?string $consumptionEan = null,
        public ?string $gridCompany = null,
        public ?string $gridAreaCode = null,
        public ?string $priceAreaCode = null,
        public ?string $productionEan = null,
        public ?string $energyTaxType = null,
        public ?string $vatType = null,
        public ?int $estimatedAnnualConsumption = null,
    ) {
    }
}
