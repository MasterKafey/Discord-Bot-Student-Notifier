<?php

namespace App\Scheduler;

use App\MessageHandler\Message\CheckEvaluationNotificationMessage;
use App\MessageHandler\Message\CheckEvaluationPreviewMessage;
use App\MessageHandler\Message\CheckStudentActivityMessage;
use App\MessageHandler\Message\CheckTeacherActivityMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class MinuteScheduler implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
            RecurringMessage::every(new \DateInterval('PT1M'), new CheckStudentActivityMessage()),
            RecurringMessage::every(new \DateInterval('PT1M'), new CheckTeacherActivityMessage()),
            RecurringMessage::every(new \DateInterval('PT1M'), new CheckEvaluationPreviewMessage()),
            RecurringMessage::every(new \DateInterval('PT1M'), new CheckEvaluationNotificationMessage()),
        );
    }
}