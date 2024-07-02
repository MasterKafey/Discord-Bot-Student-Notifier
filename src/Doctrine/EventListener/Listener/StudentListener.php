<?php

namespace App\Doctrine\EventListener\Listener;

use App\Business\ConfigBusiness;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Student::class)]
readonly class StudentListener
{
    public function __construct(
        private ConfigBusiness $configBusiness
    )
    {

    }

    public function prePersist(Student $student): void
    {
        $dayNumberInactivity = $this->configBusiness->get('inactivity_duration_day');
        $dayNumberNotification = $this->configBusiness->get('interval_notification_day');

        $student
            ->setIntervalInactivity(new \DateInterval('P' . $dayNumberInactivity . 'D'))
            ->setIntervalNotification(new \DateInterval('P' . $dayNumberNotification . 'D'))
        ;
    }
}