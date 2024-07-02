<?php

namespace App\Discord\Command\Configuration;

use App\Business\ConfigBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
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
        return 'Défini le contenu des mails envoyés aux parents';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'content',
                'required' => true,
                'type' => Option::STRING,
                'description' => "Texte de l'email",
            ],
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $content = $interaction->data->options->get('name', 'content')->value;

        $this->configBusiness->set('email_text', $content);

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Le contenu des futurs mails a bien été mis à jours !'));
    }
}