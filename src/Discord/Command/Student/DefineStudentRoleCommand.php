<?php

namespace App\Discord\Command\Student;

use App\Business\StudentBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class DefineStudentRoleCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly StudentBusiness $studentBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'set-student-role';
    }

    public function getDescription(): string
    {
        return 'Définie le role étudiant à ciblé';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'role',
                'description' => 'Le role ciblé',
                'type' => Option::ROLE,
                'required' => true,
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $roleId = $interaction->data->options->get('name', 'role')->value;

        $role = $interaction->guild->roles->offsetGet($roleId);

        if (null === $role) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Le role est invalide'));
        }

        $this->studentBusiness->setStudentRole($role);
        return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Le role étudiant est correctement défini'));
    }
}