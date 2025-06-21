<?php

namespace App\Enum;

enum ReportStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case CLOSED = 'closed';

    public function isOpen(): bool
    {
        return $this === self::OPEN;
    }

    public function isInProgress(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }
}
