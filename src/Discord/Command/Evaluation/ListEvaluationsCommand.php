<?php

namespace App\Discord\Command\Evaluation;

use App\Business\EvaluationBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Evaluation;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class ListEvaluationsCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Discord                $discord, private readonly EvaluationBusiness $evaluationBusiness
    )
    {
    }

    public function getName(): string
    {
        return 'list-evaluations';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'student',
                'type' => Option::USER,
                'required' => true,
                'description' => "L'étudiant don't vous voulez obtenir les évaluations",
            ],
            [
                'name' => 'max-average',
                'type' => Option::INTEGER,
                'required' => false,
                'description' => "Moyenne maximal"
            ],
            [
                'name' => 'starting-date',
                'type' => Option::STRING,
                'required' => false,
                'description' => "Retire les évaluations avant cette date (dd/mm/yyyy)",
            ],
            [
                'name' => 'ending-date',
                'type' => Option::STRING,
                'required' => false,
                'description' => "Retire les évaluations après cette date (dd/mm/yyyy)",
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $studentId = $interaction->data->options->get('name', 'student')->value;
        $maxAverage = $interaction->data->options->get('name', 'max-average')?->value ?? 20;
        $startingDate = $interaction->data->options->get('name', 'starting-date')?->value ?? null;
        $endingDate = $interaction->data->options->get('name', 'ending-date')?->value ?? null;

        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $studentId]);

        if (null === $student) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'utilisateur <@$studentId> n'est pas un étudiant"));
        }

        $criteria = [
            'student' => $student,
            'startingDate' => null,
            'endingDate' => null,
        ];

        if (null !== $startingDate) {
            $startingDateTime = \DateTime::createFromFormat('d/m/Y', $startingDate);
            if (!$startingDateTime) {
                return $interaction->respondWithMessage(MessageBuilder::new()->setContent("La date de début doit être au format dd/mm/yyyy"));
            }
            $startingDateTime->setTime(0, 0);
            $criteria['startingDate'] = $startingDateTime;
        }

        if (null !== $endingDate) {
            $endingDateTime = \DateTime::createFromFormat('d/m/Y', $endingDate);
            if (!$endingDateTime) {
                return $interaction->respondWithMessage(MessageBuilder::new()->setContent("La date de fin doit être au format dd/mm/yyyy"));
            }

            $endingDateTime->setTime(23,59,59);
            $criteria['endingDate'] = $endingDateTime;
        }

        $evaluations = $this->entityManager->getRepository(Evaluation::class)->search(
            student: $criteria['student'],
            startingDate: $criteria['startingDate'],
            endingDate: $criteria['endingDate'],
        );

        if (empty($evaluations)) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent('Aucune évaluation enregistrée'));
        }

        $completedEvaluations = array_filter($evaluations, function (Evaluation $evaluation) {
            return !in_array(null, [
                $evaluation->getCoefficient(),
                $evaluation->getMark(),
                $evaluation->getMaxMark(),
            ]);
        });

        $embeds = [];
        $embeds[] = (new Embed($this->discord))
            ->setAuthor("Evaluations de {$student->getUsername()}")
            ->setTitle("Moyenne des évaluations complétées : " . ($this->evaluationBusiness->getEvaluationsAverage($completedEvaluations, $maxAverage) ?? '-') . "/$maxAverage")
            ->addFieldValues('#Id', implode("\n", array_map(function (Evaluation $evaluation) {
                return $evaluation->getId();
            }, $evaluations)), true)
            ->addFieldValues('Date', implode("\n", array_map(function (Evaluation $evaluation) {
                return $evaluation->getDate()->format('d/m/Y');
            }, $evaluations)), true)
            ->addFieldValues('Note', implode("\n", array_map(function (Evaluation $evaluation) {
                return ($evaluation->getMark() ?? '-') . '/' . ($evaluation->getMaxMark() ?? '-') . " Coef {$evaluation->getCoefficient()}";
            }, $evaluations)), true);

        return $interaction->respondWithMessage(MessageBuilder::new()->setEmbeds($embeds));
    }
}