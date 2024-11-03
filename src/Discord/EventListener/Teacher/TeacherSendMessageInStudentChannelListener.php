<?php

namespace App\Discord\EventListener\Teacher;

use App\Business\ConfigBusiness;
use App\Discord\EventListener\AbstractDiscordListener;
use App\Entity\Student;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Doctrine\ORM\EntityManagerInterface;

class TeacherSendMessageInStudentChannelListener extends AbstractDiscordListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigBusiness         $configBusiness
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
        $teacherId = $this->configBusiness->get('teacher_account');

        if ($memberId !== $teacherId) {
            return;
        }

        $channelId = $message->channel_id;

        $students = $this->entityManager->getRepository(Student::class)->findBy(['channelId' => $channelId]);

        if (empty($students)) {
            return;
        }
        foreach ($students as $student) {
            $student->setUnseenMessageDateTime(null);
        }
        $this->entityManager->flush();
    }
}