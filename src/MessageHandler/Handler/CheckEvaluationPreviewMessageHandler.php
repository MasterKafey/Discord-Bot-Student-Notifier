<?php

namespace App\MessageHandler\Handler;

use App\Business\ConfigBusiness;
use App\Entity\Evaluation;
use App\Factory\DiscordFactory;
use App\MessageHandler\Message\CheckEvaluationPreviewMessage;
use Discord\Builders\MessageBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function React\Promise\all;

#[AsMessageHandler]
class CheckEvaluationPreviewMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigBusiness         $configBusiness,
        private readonly string                 $discordBotToken
    )
    {

    }

    public function __invoke(CheckEvaluationPreviewMessage $message): void
    {
        $interval = new \DateInterval($this->configBusiness->get('evaluation_preview_interval'));

        $evaluations = $this->entityManager->getRepository(Evaluation::class)->getPreviewToSend($interval);

        if (empty($evaluations)) {
            return;
        }
        $discord = DiscordFactory::getDiscord($this->discordBotToken);

        $discord->on('init', function () use ($evaluations, $discord) {
            $promises = [];
            foreach ($evaluations as $evaluation) {
                $channelId = $evaluation->getStudent()->getChannelId();
                $studentId = $evaluation->getStudent()->getMemberId();

                $promises[] = $discord->getChannel($channelId)->sendMessage(MessageBuilder::new()->setContent(
                    "<@$studentId>, une évaluation est prévue pour le " . $evaluation->getDate()->format('d/m/Y')
                ));
            }

            return all($promises)->finally(function () use ($discord) {
               $discord->close();
            });
        });

        $discord->run();

        foreach ($evaluations as $evaluation) {
            $evaluation->setPreviewSent(true);
        }

        $this->entityManager->flush();
    }
}