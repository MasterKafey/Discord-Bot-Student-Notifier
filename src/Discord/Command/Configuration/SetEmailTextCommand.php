<?php

namespace App\Discord\Command\Configuration;

use App\Business\ConfigBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class SetEmailTextCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly ConfigBusiness $configBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'set-email-text';
    }

    public function getDescription(): string
    {
        return 'Définie le contenu des mails envoyés aux parents';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'message-id',
                'description' => "Message's id which will be use to define email content",
                'required' => true,
                'type' => Option::STRING,
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $messageId = $interaction->data->options->get('name', 'message-id')->value;

        return $interaction->channel->messages->fetch($messageId)->then(function (Message $message) use ($interaction) {
            $content = $message->content;

            $this->configBusiness->set('email_text', $content);

            return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Le contenu des futurs mails a bien été mis à jours !'));
        }, function () use ($interaction) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent(
                implode("\n", [
                    'Le message ciblé est inaccessible.',
                    "Assurez-vous que la commande est executé dans le même salon que le message et que le message n'est pas trop ancien."
                ]))
            );
        });
    }
}