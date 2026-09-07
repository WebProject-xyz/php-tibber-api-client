<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class HomeFeatures
{
    public function __construct(
        public ?bool $realTimeConsumptionEnabled = null,
    ) {
    }
}
