<?php

namespace App\Enum;

enum CardContentType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case LATEX = 'latex';
    case EMBED = 'embed';

    public function isText(): bool
    {
        return $this === self::TEXT;
    }

    public function isImage(): bool
    {
        return $this === self::IMAGE;
    }

    public function isAudio(): bool
    {
        return $this === self::AUDIO;
    }

    public function isVideo(): bool
    {
        return $this === self::VIDEO;
    }

    public function isLatex(): bool
    {
        return $this === self::LATEX;
    }

    public function isEmbed(): bool
    {
        return $this === self::EMBED;
    }
}
