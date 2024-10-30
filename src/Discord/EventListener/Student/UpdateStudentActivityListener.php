<?php

namespace App\Discord\EventListener\Student;

use App\Discord\EventListener\AbstractDiscordListener;
use App\Entity\Student;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Doctrine\ORM\EntityManagerInterface;

class UpdateStudentActivityListener extends AbstractDiscordListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {

    }

    public function getDiscordEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function __invoke(Message $message): void
    {
        $memberId = $message->user_id;
        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $memberId]);

        if (null === $student) {
            return;
        }

        $this->entityManager->detach($student);
        $student = $this->entityManager->getRepository(Student::class)->find($student->getId());

        if ($student->getChannelId() !== $message->channel_id) {
            return;
        }

        $student
            ->setLastActivityDateTime(new \DateTime())
            ->setLastNotification(null)
            ->setCurrentNotificationBeforeMail($student->getNotificationBeforeMail())
        ;

        if (null === $student->getUnseenMessageDateTime()) {
            $student->setUnseenMessageDateTime(new \DateTime());
        }

        $this->entityManager->persist($student);
        $this->entityManager->flush();
    }
}