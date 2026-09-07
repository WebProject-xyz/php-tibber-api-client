<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model\Enum;

enum PriceRatingLevel: string
{
    case LOW    = 'LOW';
    case NORMAL = 'NORMAL';
    case HIGH   = 'HIGH';
}
