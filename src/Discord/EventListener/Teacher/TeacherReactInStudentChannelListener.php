<?php

namespace App\Discord\EventListener\Teacher;

use App\Business\ConfigBusiness;
use App\Discord\EventListener\AbstractDiscordListener;
use App\Entity\Student;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\MessageReaction;
use Discord\WebSockets\Event;
use Doctrine\ORM\EntityManagerInterface;

class TeacherReactInStudentChannelListener extends AbstractDiscordListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConfigBusiness         $configBusiness
    )
    {

    }

    public function getDiscordEvent(): string
    {
        return Event::MESSAGE_REACTION_ADD;
    }

    public function __invoke(MessageReaction $messageReaction): void
    {
        $memberId = $messageReaction->user_id;
        $teacherId = $this->configBusiness->get('teacher_account');

        if ($memberId !== $teacherId) {
            return;
        }

        $channelId = $messageReaction->channel_id;

        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['channelId' => $channelId]);

        if (null === $student) {
            return;
        }

        $student->setUnseenMessageDateTime(null);
        $this->entityManager->flush();
    }
}