<?php

namespace App\Enum;

enum Theme: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
    case AUTO = 'auto';
}