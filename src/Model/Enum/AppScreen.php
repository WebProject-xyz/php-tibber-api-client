<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Model\Enum;

enum AppScreen: string
{
    case HOME             = 'HOME';
    case REPORTS          = 'REPORTS';
    case CONSUMPTION      = 'CONSUMPTION';
    case COMPARISON       = 'COMPARISON';
    case DISAGGREGATION   = 'DISAGGREGATION';
    case HOME_PROFILE     = 'HOME_PROFILE';
    case CUSTOMER_PROFILE = 'CUSTOMER_PROFILE';
    case METER_READING    = 'METER_READING';
    case NOTIFICATIONS    = 'NOTIFICATIONS';
    case INVOICES         = 'INVOICES';
}
