<?php

namespace App\Discord\Command\Student;

use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class DeleteStudentCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {

    }

    public function getName(): string
    {
        return 'delete-student';
    }

    public function getDescription(): string
    {
        return 'Supprimer un étudiant de la base de données';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'member-id',
                'description' => "L'id du membre Discord",
                'required' => true,
                'type' => Option::INTEGER,
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $id = $interaction->data->options->get('name', 'member-id')?->value;

        if ($id <= 0) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'identifiant est inférieur à 0"));
        }

        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $id]);

        if (null === $student) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'identifiant n'est rattaché à aucun utilisateur"));
        }

        $this->entityManager->remove($student);
        $this->entityManager->flush();

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'étudiant a été supprimé de la base de données"));
    }
}