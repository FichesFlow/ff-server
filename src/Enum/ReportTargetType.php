<?php

namespace App\Enum;

enum ReportTargetType: string
{
    case USER = 'user';
    case POST = 'post';
    case COMMENT = 'comment';
    case OTHER = 'other';
}
