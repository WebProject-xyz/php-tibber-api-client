<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class PriceInfo
{
    /**
     * @param array<Price> $today
     * @param array<Price> $tomorrow
     */
    public function __construct(
        public ?Price $current = null,
        public array $today = [],
        public array $tomorrow = [],
    ) {
    }
}
