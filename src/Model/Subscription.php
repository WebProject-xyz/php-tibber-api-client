<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

use DateTimeImmutable;

final readonly class Subscription
{
    public function __construct(
        public ?string $id = null,
        public ?LegalEntity $subscriber = null,
        public ?DateTimeImmutable $validFrom = null,
        public ?DateTimeImmutable $validTo = null,
        public ?string $status = null,
        public ?PriceInfo $priceInfo = null,
    ) {
    }
}
