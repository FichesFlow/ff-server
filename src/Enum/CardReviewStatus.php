<?php

namespace App\Enum;

enum CardReviewStatus: string
{
    case NEW = 'new';
    case DUE = 'due';
    case LEARNED = 'learned';
    case ARCHIVED = 'archived';
    case DELETED = 'deleted';
    case NEVER_SEEN = 'never_seen';
    case NOT_DUE_YET = 'not_due_yet';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->getLabel()] = $case->value;
        }
        return $options;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::DUE => 'Due',
            self::LEARNED => 'Learned',
            self::ARCHIVED => 'Archived',
            self::DELETED => 'Deleted',
            self::NEVER_SEEN => 'Never Seen',
            self::NOT_DUE_YET => 'Not Due Yet',
        };
    }
}
