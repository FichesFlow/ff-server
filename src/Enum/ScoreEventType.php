<?php

namespace App\Enum;

enum ScoreEventType: string
{
    case DECK_PUBLISH = 'deck_publish';
    case DAILY_LOGIN = 'daily_login';
    case COMPLETE_STUDY_SESSION = 'complete_study_session';
    case STREAK_MILESTONE = 'streak_milestone';
    case PROFILE_COMPLETE = 'profile_complete';
    case ACHIEVEMENT_UNLOCK = 'achievement_unlock';

    public function getLabel(): string
    {
        return match ($this) {
            self::DECK_PUBLISH => 'Publish a deck',
            self::DAILY_LOGIN => 'Daily login',
            self::COMPLETE_STUDY_SESSION => 'Complete study session',
            self::STREAK_MILESTONE => 'Reach a streak milestone',
            self::PROFILE_COMPLETE => 'Complete profile',
            self::ACHIEVEMENT_UNLOCK => 'Unlock an achievement',
        };
    }

    public function getDefaultPoints(): int
    {
        return match ($this) {
            self::DECK_PUBLISH => 20,
            self::DAILY_LOGIN => 10,
            self::COMPLETE_STUDY_SESSION => 25,
            self::STREAK_MILESTONE => 50,
            self::PROFILE_COMPLETE => 30,
            self::ACHIEVEMENT_UNLOCK => 40,
        };
    }
}
