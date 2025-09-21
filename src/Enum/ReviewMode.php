<?php

namespace App\Enum;

enum ReviewMode: string
{
    case FLASHCARD = 'flashcard';
    case QCM = 'qcm';
    case DICTAPHONE = 'dictaphone';
}
