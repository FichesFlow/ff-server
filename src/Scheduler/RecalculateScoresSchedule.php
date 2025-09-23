<?php
// src/Scheduler/RecalculateScoresSchedule.php

namespace App\Scheduler;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('main')]
class RecalculateScoresSchedule implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return new Schedule()
            ->add(RecurringMessage::cron('0 0 * * *', new RunCommandMessage(
                'app:recalculate-user-scores --batch-size=50',
            )));
    }
}
