<?php

namespace App\Discord\Command\Evaluation;

use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Evaluation;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class DeleteEvaluationCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {

    }

    public function getName(): string
    {
        return 'delete-evaluation';
    }

    public function getDescription(): string
    {
        return 'Supprimer une évaluation';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'student',
                'description' => "L'étudiant concerné par l'évaluation",
                'type' => Option::USER,
                'required' => true,
            ],
            [
                'name' => 'id',
                'description' => "Identifiant de l'évaluation",
                'type' => Option::INTEGER,
                'required' => true,
            ],
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        // Student id is used only to check teacher don't delete another student evaluation
        $studentId = $interaction->data->options->get('name', 'student')->value;
        $evaluationId = $interaction->data->options->get('name', 'id')->value;

        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $studentId]);
        if (null === $student) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'utilisateur <@$studentId> n'est pas un étudiant"));
        }

        $evaluation = $this->entityManager->getRepository(Evaluation::class)->findOneBy(['id' => $evaluationId, 'student' => $student]);

        if (null === $evaluation) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Aucune évaluation avec l'id '$evaluationId' n'existe pour '{$student->getUsername()}'"));
        }

        $this->entityManager->remove($evaluation);
        $this->entityManager->flush();

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'évaluation avec l'id '$evaluationId' de l'étudiant {$student->getUsername()} a été supprimé avec succés !"));
    }
}