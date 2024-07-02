<?php

namespace App\Discord\Command\Configuration;

use App\Business\ConfigBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class SetEmailSubjectCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly ConfigBusiness $configBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'set-email-subject';
    }

    public function getDescription(): string
    {
        return "Définie le sujet de l'email";
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'subject',
                'type' => Option::STRING,
                'required' => true,
                'description' => "Le sujet de l'email",
            ],
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $subject = $interaction->data->options->get('name', 'subject')->value;

        $this->configBusiness->set('email_subject', $subject);

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent(
            "Le sujet des futurs emails à été mis à jours"
        ));
    }
}