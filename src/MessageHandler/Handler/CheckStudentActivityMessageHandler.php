<?php

namespace App\MessageHandler\Handler;

use App\Business\ConfigBusiness;
use App\Entity\Student;
use App\Factory\DiscordFactory;
use App\MessageHandler\Message\AskEmailToSendMessage;
use App\MessageHandler\Message\CheckStudentActivityMessage;
use App\MessageHandler\Message\SendEmailToParentsMessage;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use function React\Promise\all;

#[AsMessageHandler]
class CheckStudentActivityMessageHandler
{
    public function __construct(
        private readonly ConfigBusiness         $configBusiness,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface    $messageBus,
        private readonly string                 $discordBotToken,
    )
    {
    }

    public
    function __invoke(CheckStudentActivityMessage $message): void
    {
        dump('test');
        /** @var Student[] $students */
        $students = $this->entityManager->getRepository(Student::class)->findBy(['tracking' => true]);
        $now = new \DateTime();
        $studentsToNotify = [];
        $studentsToSendMail = [];
        foreach ($students as $student) {
            $lastNotification = $student->getLastNotification();
            $lastActivityDateTime = $student->getLastActivityDateTime();
            $intervalNotification = $student->getIntervalNotification();
            $intervalInactivity = $student->getIntervalInactivity();

            $inactivityDeadline = (clone $lastActivityDateTime)->add($intervalInactivity);
            if ($now >= $inactivityDeadline) {
                $sendNotification = $lastNotification === null || $now >= (clone $lastNotification)->add($intervalNotification);

                if ($sendNotification) {
                    $studentsToNotify[] = $student;
                    $student->setLastNotification($now);
                    if ($student->getCurrentNotificationBeforeMail() === 0) {
                        $studentsToSendMail[] = $student;
                    }

                    $student->setCurrentNotificationBeforeMail($student->getNotificationBeforeMail() - 1);
                    $this->entityManager->persist($student);
                }
            }
        }

        if (!empty($studentsToNotify)) {
            $this->sendNotifications($studentsToNotify);
        }

        if (!empty($studentsToSendMail)) {
            if ($this->configBusiness->get('automatic_emails')) {
                $this->sendEmails($studentsToSendMail);
            } else {
                $this->sendAskMessageToSendEmail($studentsToSendMail);
            }
        }

        $this->entityManager->flush();
    }

    /** @param Student[] $students */
    private
    function sendNotifications(array $students): void
    {
        $discord = DiscordFactory::getDiscord($this->discordBotToken);
        $discord->on('ready', function () use ($students, $discord) {
            $promises = [];

            foreach ($students as $student) {
                $channelId = $student->getChannelId();
                /** @var Channel $channel */
                $channel = $discord->getChannel($channelId);
                if (null === $channel || !$channel->getBotPermissions()->view_channel || !$channel->getBotPermissions()->send_messages) {
                    $lines = [
                        "Le salon <#$channelId> de l'étudiant <@{$student->getMemberId()}> n'est pas accessible.",
                        "Si cela est normal, vous pouvez désactiver le tracking avec la commande /toggle-tracking <@{$student->getMemberId()}>.",
                        "Si l'utilisateur n'est plus sur le serveur dans ce cas utilisez cette commande /delete-student {$student->getMemberId()}",
                    ];

                    $promises[] = $discord->getChannel($this->configBusiness->get('output_channel'))->sendMessage(MessageBuilder::new()->setContent(implode("\n", $lines)));
                    continue;
                }

                $promises[] = $channel->sendMessage("<@{$student->getMemberId()}>, vous n'êtes plus actif depuis le " . $student->getLastActivityDateTime()->format('d/m/Y'));
            }

            all($promises)->then(function () use ($discord) {
                $discord->close();
                $this->entityManager->flush();
            }, function () use ($discord) {
                $discord->close();
            });
        });

        $discord->run();
    }

    /** @param Student[] $students */
    public function sendEmails(array $students): void
    {
        foreach ($students as $student) {
            if (null !== $student->getEmailAddress()) {
                $this->messageBus->dispatch(new SendEmailToParentsMessage($student));
            }
        }
    }

    /** @param Student[] $students */
    public function sendAskMessageToSendEmail(array $students): void
    {
        foreach ($students as $student) {
            if (null !== $student->getEmailAddress()) {
                $this->messageBus->dispatch(new AskEmailToSendMessage($student));
            }
        }
    }
}