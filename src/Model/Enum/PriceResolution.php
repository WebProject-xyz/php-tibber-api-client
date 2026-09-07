<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model\Enum;

enum PriceResolution: string
{
    case HOURLY         = 'HOURLY';
    case QUARTER_HOURLY = 'QUARTER_HOURLY';
}
