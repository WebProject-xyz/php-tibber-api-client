<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model\Enum;

enum EnergyResolution: string
{
    case HOURLY  = 'HOURLY';
    case DAILY   = 'DAILY';
    case WEEKLY  = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case ANNUAL  = 'ANNUAL';
}
