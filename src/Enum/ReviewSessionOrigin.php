<?php

namespace App\Enum;

enum ReviewSessionOrigin: string
{
    case MANUAL = 'manual';
    case QUEUE = 'queue';
}
