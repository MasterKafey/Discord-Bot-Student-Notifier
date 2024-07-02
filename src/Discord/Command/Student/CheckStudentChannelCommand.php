<?php

namespace App\Discord\Command\Student;

use App\Business\ChannelBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class CheckStudentChannelCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ChannelBusiness        $channelBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'check-student-channel';
    }

    public function getDescription(): string
    {
        return "Localise le salon d'un étudiant";
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'student',
                'type' => Option::USER,
                'description' => "L'étudiant ciblé",
                'required' => true,
            ],
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $memberId = $interaction->data->options->get('name', 'student')->value;

        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $memberId]);

        if (null === $student) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent(implode("\n", [
                "Cet utilisateur n'est pas un étudiant.",
                "Si cela est une erreur, utilisez la commande /add-student <@$memberId> [salon] pour corriger ce probléme"
            ])));
        }

        $channelId = $student->getChannelId();

        if (
            null === $channelId ||
            null === ($channel = $this->channelBusiness->getChannel($channelId)) ||
            !$channel->getBotPermissions()->view_channel ||
            !$channel->getBotPermissions()->send_messages
        ) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent(implode("\n", [
                "Le salon de l'étudiant n'est pas accessible",
                "Vous pouvez définir le salon de l'étudiant avec la commande /add-student <@$memberId> [channel]"
            ])));
        }

        return $channel->sendMessage(MessageBuilder::new()->setContent("Le salon de l'étudiant <@$memberId> est ici"))->then(function () use ($interaction, $channelId) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Le salon de l'étudiant est défini ici : <#$channelId>"));
        });
    }
}