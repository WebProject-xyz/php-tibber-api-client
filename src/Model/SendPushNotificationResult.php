<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model;

final readonly class SendPushNotificationResult
{
    public function __construct(
        public bool $successful,
        public int $pushedToNumberOfDevices,
    ) {
    }
}
