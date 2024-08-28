<?php

namespace App\Discord\Command\Evaluation;

use App\Business\ConfigBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Evaluation;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class CreateEvaluationCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, private readonly ConfigBusiness $configBusiness
    )
    {
    }

    public function getName(): string
    {
        return 'create-evaluation';
    }

    public function getDescription(): string
    {
        return 'Créer une évaluation';
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
                'name' => 'date',
                'description' => "Date de l'évaluation",
                'type' => Option::STRING,
                'required' => true,
            ],
            [
                'name' => 'mark',
                'description' => "Note de l'étudiant",
                'type' => Option::NUMBER,
                'required' => false,
            ],
            [
                'name' => 'max-mark',
                'description' => "Note maximum de l'évaluation",
                'type' => Option::INTEGER,
                'required' => false,
            ],
            [
                'name' => 'coefficient',
                'description' => "Coefficient de l'évaluation",
                'type' => Option::INTEGER,
                'required' => false,
            ]
        ];
    }

    public function execute(Interaction $interaction): PromiseInterface
    {
        $studentId = $interaction->data->options->get('name', 'student')->value;
        $date = $interaction->data->options->get('name', 'date')->value;
        $mark = $interaction->data->options->get('name', 'mark')?->value;
        $maxMark = $interaction->data->options->get('name', 'max-mark')?->value;
        $coefficient = $interaction->data->options->get('name', 'coefficient')?->value;

        $datetime = \DateTime::createFromFormat("d/m/Y", $date);

        if (!$datetime) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("La date doit être au format dd/mm/yyyy"));
        }
        $datetime->setTime(17, 0);
        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $studentId]);

        if (null === $student) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'utilisateur <@$studentId> n'est pas un étudiant"));
        }

        $evaluation = (new Evaluation())
            ->setStudent($student)
            ->setMark($mark)
            ->setMaxMark($maxMark)
            ->setCoefficient($coefficient)
            ->setDate($datetime);

        $evaluation
            ->setPreviewSent((clone $datetime)->add(new \DateInterval($this->configBusiness->get('evaluation_preview_interval'))) < (new \DateTime()))
            ->setNotificationSent($datetime < (new \DateTime()));

        $this->entityManager->persist($evaluation);
        $this->entityManager->flush();

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'évaluation a été crée avec succès"));
    }
}