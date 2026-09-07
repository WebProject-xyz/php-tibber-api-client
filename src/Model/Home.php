<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

use WebProject\TibberApiClient\Model\Enum\HeatingSource;
use WebProject\TibberApiClient\Model\Enum\HomeType;

final readonly class Home
{
    /**
     * @param array<Subscription> $subscriptions
     */
    public function __construct(
        public string $id,
        public ?string $timeZone = null,
        public ?string $appNickname = null,
        public ?string $appAvatar = null,
        public ?int $size = null,
        public ?HomeType $type = null,
        public ?int $numberOfResidents = null,
        public ?HeatingSource $primaryHeatingSource = null,
        public ?bool $hasVentilationSystem = null,
        public ?int $mainFuseSize = null,
        public ?Address $address = null,
        public ?LegalEntity $owner = null,
        public ?MeteringPointData $meteringPointData = null,
        public ?Subscription $currentSubscription = null,
        public array $subscriptions = [],
        public ?HomeFeatures $features = null,
    ) {
    }
}
