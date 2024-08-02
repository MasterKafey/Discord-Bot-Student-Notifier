<?php

namespace App\Discord\Command\Student;

use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class ToggleTrackStudentCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {

    }

    public function getName(): string
    {
        return 'toggle-tracking-student';
    }

    public function getDescription(): string
    {
        return "Activé/Désactivé la surveillance d'un étudiant";
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'student',
                'type' => Option::USER,
                'required' => true,
                'description' => "L'étudiant ciblé",
            ],
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $studentId = $interaction->data->options->get('name', 'student')->value;

        $student = $this->entityManager->getRepository(Student::class)->findOneBy([
            'memberId' => $studentId,
        ]);

        $student->setTracking(!$student->isTracking());
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent(
            "L'étudiant " . ($student->isTracking() ? "est de nouveau" : "n'est plus") . " suivi"
        ));
    }
}