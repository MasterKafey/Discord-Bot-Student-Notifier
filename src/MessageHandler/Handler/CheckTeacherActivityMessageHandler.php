<?php

namespace App\MessageHandler\Handler;

use App\Business\ConfigBusiness;
use App\Entity\Student;
use App\Factory\DiscordFactory;
use App\MessageHandler\Message\CheckTeacherActivityMessage;
use Discord\Builders\MessageBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function React\Promise\all;

#[AsMessageHandler]
class CheckTeacherActivityMessageHandler
{
    public function __construct(
        private readonly ConfigBusiness         $configBusiness,
        private readonly EntityManagerInterface $entityManager,
        private readonly string                 $discordBotToken,
    )
    {
    }

    public function __invoke(CheckTeacherActivityMessage $message): void
    {
        $teacherAccount = $this->configBusiness->get('teacher_account');

        if ($teacherAccount === null) {
            return;
        }

        $students = $this->entityManager->getRepository(Student::class)->findStudentsNotSeen();
        $interval = new \DateInterval($this->configBusiness->get('teacher_notification_interval'));
        $now = new \DateTime();
        $maxDateBeforeNotification = (clone $now)->sub($interval);
        $filteredStudents = [];
        /** @var Student $student */
        foreach ($students as $student) {
            if ($student->getUnseenMessageDateTime() < $maxDateBeforeNotification) {
                $filteredStudents[] = $student;
            }
        }

        if (empty($filteredStudents)) {
            return;
        }

        $discord = DiscordFactory::getDiscord($this->discordBotToken);

        $discord->on('ready', function() use ($discord, $filteredStudents, $teacherAccount) {
            $promises = [];
            $teacherUser = $discord->users->get('id', $teacherAccount);
            if ($teacherUser === null) {
                $discord->getLogger()->error("$teacherAccount is not working, try again now");
                $teacherUser = $discord->users->get('id', $teacherAccount);
                if ($teacherUser === null) {
                    $discord->getLogger()->error("$teacherAccount still not working, try again later");
                    $discord->close();
                    return;
                } else {
                    $discord->getLogger()->error('Second try worked');
                }
            }

            $lines = [];
            foreach ($filteredStudents as $student) {
                $lines[] = "L'étudiant(e) {$student->getUsername()} vous a envoyé un message dans le salon <#{$student->getChannelId()}> et vous n'avez pas réagi.";
            }
            foreach (array_chunk($lines, 10) as $lines) {
                $promises[] = $teacherUser->sendMessage(MessageBuilder::new()->setContent(implode("\n", $lines)));
            }

            all($promises)->finally(function() use ($discord) {
                $discord->close();
            });
        });

        $discord->run();

        foreach ($filteredStudents as $student) {
            $student->setUnseenMessageDateTime($now);
        }

        $this->entityManager->flush();
    }
}