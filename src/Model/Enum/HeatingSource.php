<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model\Enum;

enum HeatingSource: string
{
    case AIR2AIR_HEATPUMP   = 'AIR2AIR_HEATPUMP';
    case AIR2WATER_HEATPUMP = 'AIR2WATER_HEATPUMP';
    case BOILER             = 'BOILER';
    case CENTRAL_HEATING    = 'CENTRAL_HEATING';
    case DISTRICT           = 'DISTRICT';
    case DISTRICT_HEATING   = 'DISTRICT_HEATING';
    case ELECTRIC_BOILER    = 'ELECTRIC_BOILER';
    case ELECTRICITY        = 'ELECTRICITY';
    case FLOOR              = 'FLOOR';
    case GAS                = 'GAS';
    case GROUND             = 'GROUND';
    case OIL                = 'OIL';
    case OTHER              = 'OTHER';
    case WASTE              = 'WASTE';
}
