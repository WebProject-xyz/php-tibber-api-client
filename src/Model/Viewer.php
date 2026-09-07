<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class Viewer
{
    /**
     * @param array<Home> $homes
     */
    public function __construct(
        public ?string $login = null,
        public ?string $userId = null,
        public ?string $name = null,
        public ?string $websocketSubscriptionUrl = null,
        public array $homes = [],
    ) {
    }
}
