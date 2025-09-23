<?php

namespace App\Enum;

enum ReviewPriority: string
{
    case NORMAL = 'normal';
    case INTENSE = 'intense';

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
            self::NORMAL => 'Normal',
            self::INTENSE => 'Intense',
        };
    }
}
