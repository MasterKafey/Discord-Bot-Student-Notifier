<?php

namespace App\Discord\Command\Student;

use App\Business\ChannelBusiness;
use App\Business\StudentBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Permissions\Permission;
use Discord\Parts\User\Member;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;
use function React\Promise\all;

class CreateAllStudentChannels extends AbstractDiscordCommand
{
    public function __construct(
        private readonly StudentBusiness        $studentBusiness,
        private readonly ChannelBusiness        $channelBusiness,
        private readonly EntityManagerInterface $entityManager,
        private readonly Discord                $discord
    )
    {

    }

    public function getName(): string
    {
        return 'create-all-students-channel';
    }

    public function getDescription(): string
    {
        return "Création et enregistrement de tout les membres avec le role étudiant";
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $role = $this->studentBusiness->getStudentRole();

        if (null === $role) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Le role étudiant n'est pas défini"));
        }

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Début du processus"))->then(function () use ($interaction, $role) {
            $students = $this->entityManager->getRepository(Student::class)->findAll();
            $promises = [];
            /** @var Member $member */
            foreach ($interaction->guild->members as $member) {
                if (!$member->roles->offsetGet($role->id)) {
                    continue;
                }
                $currentStudent = null;
                foreach ($students as $student) {
                    if ($student->getMemberId() === $member->id) {
                        $currentStudent = $student;
                        break;
                    }
                }

                if ($currentStudent === null) {
                    $student = new Student();
                    $student
                        ->setUsername($member->username)
                        ->setMemberId($member->id);
                }

                $channelId = $student->getChannelId();

                if (null !== $channelId) {
                    $channel = $this->discord->getChannel($channelId);
                    if (null !== $channel && $channel->getBotPermissions()->view_channel && $channel->getBotPermissions()->send_messages) {
                        continue;
                    }
                }

                $bitwisePermissions = Permission::ALL_PERMISSIONS['view_channel'] | Permission::TEXT_PERMISSIONS['send_messages'];
                $promises[] = $this->channelBusiness->createChannel($interaction->guild, $member->nick ?? $member->username, permissions: [
                    [
                        'id' => $member->id,
                        'type' => 'member',
                        'allow' => $bitwisePermissions,
                    ],
                    [
                        'id' => $this->discord->id,
                        'type' => 'member',
                        'allow' => $bitwisePermissions,
                    ],
                ])->then(function (Channel $channel) use ($student) {
                    $student->setChannelId($channel->id);
                    $this->entityManager->persist($student);
                });
            }

            return all($promises)->then(function() use ($interaction) {
                $this->entityManager->flush();
                $interaction->respondWithMessage(MessageBuilder::new()->setContent('Processus terminé !'));
            });
        });
    }

}