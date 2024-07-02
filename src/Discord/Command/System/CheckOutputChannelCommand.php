<?php

namespace App\Discord\Command\System;

use App\Business\ChannelBusiness;
use App\Business\MessageBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class CheckOutputChannelCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly MessageBusiness $messageBusiness,
        private readonly ChannelBusiness $channelBusiness,
    )
    {

    }

    public function getName(): string
    {
        return 'check-output-channel';
    }

    public function getDescription(): string
    {
        return "Envoie un message dans le salon de notifications";
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $channelId = $this->channelBusiness->getOutputChannel()?->id;
        return $this->messageBusiness->log(MessageBuilder::new()->setContent("Le salon de notifications est défini ici <#$channelId>"))->then(function () use ($interaction) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Message envoyé avec succés!'));
        });
    }
}