<?php

namespace App\Discord\EventListener\Email;

use App\Discord\EventListener\AbstractDiscordListener;
use App\Entity\Student;
use App\MessageHandler\Message\SendEmailToParentsMessage;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendEmailActionListener extends AbstractDiscordListener
{
    public function __construct(
        private readonly MessageBusInterface $messageBus, private readonly EntityManagerInterface $entityManager,
    )
    {

    }

    public function getDiscordEvent(): string
    {
        return Event::INTERACTION_CREATE;
    }

    public function __invoke(Interaction $interaction): void
    {
        $customId = $interaction->data->custom_id;
        [$action, $id] = explode(':', $customId);

        if ($action !== 'send') {
            return;
        }

        $student = $this->entityManager->getRepository(Student::class)->find($id);

        if (null === $student) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'étudiant n'existe plus dans la base de données"));
            return;
        }

        $this->messageBus->dispatch(new SendEmailToParentsMessage($student));

        $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'email est en cours d'envoie"));
    }
}