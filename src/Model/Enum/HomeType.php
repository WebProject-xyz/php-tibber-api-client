<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model\Enum;

enum HomeType: string
{
    case APARTMENT = 'APARTMENT';
    case ROWHOUSE  = 'ROWHOUSE';
    case HOUSE     = 'HOUSE';
    case COTTAGE   = 'COTTAGE';
}
