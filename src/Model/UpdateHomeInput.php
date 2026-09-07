<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

use WebProject\TibberApiClient\Model\Enum\HeatingSource;
use WebProject\TibberApiClient\Model\Enum\HomeType;

final readonly class UpdateHomeInput
{
    public function __construct(
        public string $homeId,
        public ?string $appNickname = null,
        public ?string $appAvatar = null,
        public ?int $size = null,
        public ?HomeType $type = null,
        public ?int $numberOfResidents = null,
        public ?HeatingSource $primaryHeatingSource = null,
        public ?bool $hasVentilationSystem = null,
        public ?int $mainFuseSize = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['homeId' => $this->homeId];

        if (null !== $this->appNickname) {
            $data['appNickname'] = $this->appNickname;
        }
        if (null !== $this->appAvatar) {
            $data['appAvatar'] = $this->appAvatar;
        }
        if (null !== $this->size) {
            $data['size'] = $this->size;
        }
        if (null !== $this->type) {
            $data['type'] = $this->type->value;
        }
        if (null !== $this->numberOfResidents) {
            $data['numberOfResidents'] = $this->numberOfResidents;
        }
        if (null !== $this->primaryHeatingSource) {
            $data['primaryHeatingSource'] = $this->primaryHeatingSource->value;
        }
        if (null !== $this->hasVentilationSystem) {
            $data['hasVentilationSystem'] = $this->hasVentilationSystem;
        }
        if (null !== $this->mainFuseSize) {
            $data['mainFuseSize'] = $this->mainFuseSize;
        }

        return $data;
    }
}
