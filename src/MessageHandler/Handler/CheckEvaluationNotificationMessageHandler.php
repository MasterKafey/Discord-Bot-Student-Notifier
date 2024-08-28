<?php

namespace App\MessageHandler\Handler;

use App\Business\ConfigBusiness;
use App\Entity\Evaluation;
use App\Factory\DiscordFactory;
use App\MessageHandler\Message\CheckEvaluationNotificationMessage;
use App\MessageHandler\Message\CheckEvaluationPreviewMessage;
use Discord\Builders\MessageBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function React\Promise\all;

#[AsMessageHandler]
class CheckEvaluationNotificationMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string                 $discordBotToken
    )
    {

    }

    public function __invoke(CheckEvaluationNotificationMessage $message): void
    {
        $evaluations = $this->entityManager->getRepository(Evaluation::class)->getNotNotifyEvaluations();

        if (empty($evaluations)) {
            return;
        }

        $discord = DiscordFactory::getDiscord($this->discordBotToken);

        $discord->on('ready', function () use ($evaluations, $discord) {
            $promises = [];
            foreach ($evaluations as $evaluation) {
                $channelId = $evaluation->getStudent()->getChannelId();
                $studentId = $evaluation->getStudent()->getMemberId();

                $promises[] = $discord->getChannel($channelId)->sendMessage(MessageBuilder::new()->setContent(
                    "<@$studentId>, votre évaluation a lieu aujourd'hui !"
                ));
            }

            return all($promises)->always(function () use ($discord) {
                $discord->close();
            });
        });

        foreach ($evaluations as $evaluation) {
            $evaluation->setNotificationSent(true);
        }

        $this->entityManager->flush();
        $discord->run();
    }
}