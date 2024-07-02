<?php

namespace App\Discord\Command\Student;

use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class DefineStudentInactivityDelayCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {

    }

    public function getName(): string
    {
        return 'set-student-inactivity-delay';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'student',
                'required' => true,
                'description' => "L'étudiant ciblé",
                'type' => Option::USER,
            ],
            [
                'name' => 'day',
                'required' => true,
                'description' => "Nombre de jours d'inactivité",
                'type' => Option::NUMBER,
            ]
        ];
    }

    public function getDescription(): string
    {
        return "Définir le délais d'inactivité avant notification";
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $studentId = $interaction->data->options->get('name', 'student')->value;
        $dayNumber = $interaction->data->options->get('name','day')->value;

        if (!is_numeric($dayNumber) || !is_int(intval($dayNumber))) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("$dayNumber n'est pas un nombre valide"));
        }

        $dayNumber = intval($dayNumber);

        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $studentId]);

        if (null === $student) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("<@$studentId> n'est pas un étudiant, utiliser la commande /set-student"));
        }

        $student->setIntervalInactivity(new \DateInterval('P' . $dayNumber . 'D'));
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Le nombre de jours d'inactivité avant notification est défini à $dayNumber"));
    }
}