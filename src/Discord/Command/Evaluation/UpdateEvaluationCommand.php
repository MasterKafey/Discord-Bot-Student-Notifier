<?php

namespace App\Discord\Command\Evaluation;

use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Evaluation;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

class UpdateEvaluationCommand extends AbstractDiscordCommand
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getName(): string
    {
        return 'update-evaluation';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'id',
                'type' => Option::INTEGER,
                'description' => "Id de l'évaluation",
                'required' => true,
            ],
            [
                'name' => 'mark',
                'type' => Option::NUMBER,
                'description' => "Note de l'évaluation",
                'required' => false,
            ],
            [
                'name' => 'max-mark',
                'type' => Option::INTEGER,
                'description' => "Note maximal de l'évaluation",
                'required' => false,
            ],
            [
                'name' => 'coefficient',
                'type' => Option::INTEGER,
                'description' => "Coefficient de l'évaluation",
                'required' => false,
            ],
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $id = $interaction->data->options->get('name', 'id')->value;
        $mark = $interaction->data->options->get('name', 'mark')?->value;
        $maxMark = $interaction->data->options->get('name', 'max-mark')?->value;
        $coefficient = $interaction->data->options->get('name', 'coefficient')?->value;

        $evaluation = $this->entityManager->getRepository(Evaluation::class)->find($id);
        if (null === $evaluation) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'évaluation avec l'id '$id' n'existe pas, utilisez la commande /list-evaluations pour obtenir l'id de l'évaluation"));
        }

        $edited = false;
        if (null !== $mark) {
            $edited = true;
            $evaluation->setMark($mark);
        }

        if (null !== $maxMark) {
            $edited = true;
            $evaluation->setMaxMark($maxMark);
        }

        if (null !== $coefficient) {
            $edited = true;
            $evaluation->setCoefficient($coefficient);
        }

        if (!$edited) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Vous devez fournir une information à mettre à jour dans l'évaluation"));
        }

        $this->entityManager->persist($evaluation);
        $this->entityManager->flush();

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'évaluation a été mis à jour avec succès!"));
    }
}