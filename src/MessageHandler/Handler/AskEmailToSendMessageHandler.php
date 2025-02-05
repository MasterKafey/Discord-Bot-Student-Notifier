<?php

namespace App\MessageHandler\Handler;

use App\Business\ConfigBusiness;
use App\Factory\DiscordFactory;
use App\MessageHandler\Message\AskEmailToSendMessage;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AskEmailToSendMessageHandler
{
    public function __construct(
        private readonly string         $discordBotToken,
        private readonly ConfigBusiness $configBusiness
    )
    {

    }

    public function __invoke(AskEmailToSendMessage $message): void
    {
        $discord = DiscordFactory::getDiscord($this->discordBotToken);

        $discord->on('init', function (Discord $discord) use ($message) {
            $outputChannelId = $this->configBusiness->get('output_channel');
            $channel = $discord->getChannel($outputChannelId);

            $sendButton = Button::new(Button::STYLE_PRIMARY)
                ->setLabel('Envoyer')
                ->setCustomId('send:' . $message->getStudent()->getId());

            $channel->sendMessage(MessageBuilder::new()
                ->setContent("L'étudiant <@{$message->getStudent()->getMemberId()}> n'est plus actif, un email est en attente d'envoi")
                ->addComponent(ActionRow::new()->addComponent($sendButton))
            )->finally(function () use ($discord) {
                $discord->close();
            });
        });

        $discord->run();
    }
}