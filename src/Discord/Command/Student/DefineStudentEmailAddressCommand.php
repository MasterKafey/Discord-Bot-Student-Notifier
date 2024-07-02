<?php

namespace App\Discord\Command\Student;

use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

class DefineStudentEmailAddressCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {

    }

    public function getName(): string
    {
        return 'set-student-email';
    }

    public function getDescription(): string
    {
        return "Définie l'adresse email à utiliser en cas d'inactivité prolongé";
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'student',
                'type' => Option::USER,
                'required' => true,
                'description' => "L'étudiant à modifier"
            ],
            [
                'name' => 'email',
                'type' => Option::STRING,
                'required' => true,
                'description' => "L'adresse email utilisé pour notifier"
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $studentId = $interaction->data->options->get('name', 'student')->value;
        $email = $interaction->data->options->get('name', 'email')->value;

        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $studentId]);
        if (null === $student) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent(implode("\n", [
                "L'utilisateur <@$studentId> n'est pas un étudiant.",
                "Utilisez la commande /set-student <@$studentId> pour corriger cette erreur"
            ])));
        }
        $validator = Validation::createValidator();

        $constraints = [
            new NotBlank(),
            new Email(),
        ];

        $violations = $validator->validate($email, $constraints);

        if ($violations->count() > 0) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent(
                "L'adresse email '$email' n'est pas une adresse mail valide"
            ));
        }

        $student->setEmailAddress($email);
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'adresse email a bien été enregistré pour l'étudiant <@$studentId>"));
    }
}