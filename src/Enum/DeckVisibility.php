<?php

namespace App\Enum;

enum DeckVisibility: string
{
    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case PRIVATE = 'private';

    public function isPublic(): bool
    {
        return $this === self::PUBLIC;
    }

    public function isUnlisted(): bool
    {
        return $this === self::UNLISTED;
    }

    public function isPrivate(): bool
    {
        return $this === self::PRIVATE;
    }
}
