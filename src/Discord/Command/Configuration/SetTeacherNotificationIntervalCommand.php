<?php

namespace App\Discord\Command\Configuration;

use App\Business\ConfigBusiness;
use App\Business\StudentBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class SetTeacherNotificationIntervalCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly ConfigBusiness $configBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'set-teacher-hour-notification';
    }

    public function getDescription(): string
    {
        return "Définie l'interval de temps entre le message de l'étudiant et la notification";
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'hour',
                'description' => "Le nombre d'heures avant la notification",
                'type' => Option::INTEGER,
                'required' => true,
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $hour = $interaction->data->options->get('name', 'hour')->value;

        if ($hour <= 0) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Le nombre d'heure doit être suppérieur à 0"));
        }

        $this->configBusiness->set('teacher_notification_interval', "PT{$hour}H");
        return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Le professeur est correctement défini'));
    }
}