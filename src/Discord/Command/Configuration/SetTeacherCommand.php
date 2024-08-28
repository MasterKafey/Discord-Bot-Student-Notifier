<?php

namespace App\Discord\Command\Configuration;

use App\Business\ConfigBusiness;
use App\Business\StudentBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class SetTeacherCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly StudentBusiness $studentBusiness,
        private readonly ConfigBusiness $configBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'set-teacher';
    }

    public function getDescription(): string
    {
        return 'Définie le professeur';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'teacher',
                'description' => 'Le professeur ciblé',
                'type' => Option::USER,
                'required' => true,
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $teacherId = $interaction->data->options->get('name', 'teacher')->value;

        $this->configBusiness->set('teacher_account', $teacherId);
        return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Le professeur est correctement défini'));
    }
}