<?php

namespace App\Enum;

enum CardSides: string
{
    case FRONT = 'front';
    case BACK = 'back';

    public function isFront(): bool
    {
        return $this === self::FRONT;
    }

    public function isBack(): bool
    {
        return $this === self::BACK;
    }
}
