<?php

namespace App\Discord\Command\Configuration;

use App\Business\ConfigBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class ToggleEmailCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly ConfigBusiness $configBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'toggle-automatic-emails';
    }

    public function getDescription(): string
    {
        return "Activé/Désactivé l'envoi automatique des emails";
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $newValue = !$this->configBusiness->get('automatic_emails');
        $this->configBusiness->set('automatic_emails', $newValue);

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent(
            $newValue ? 'Les emails seront envoyés automatiquement' : "Une confirmation de l'utilisateur sera demandé avant l'envoie"
        ));
    }
}