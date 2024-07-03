<?php

namespace App\Discord\Command\Student;

use App\Business\ChannelBusiness;
use App\Business\StudentBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use App\Entity\Student;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Permissions\Permission;
use Discord\Parts\User\Member;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class DefineMemberAsStudent extends AbstractDiscordCommand
{
    public function __construct(
        private readonly StudentBusiness        $studentBusiness,
        private readonly EntityManagerInterface $entityManager,
        private readonly ChannelBusiness        $channelBusiness,
        private readonly Discord                $discord
    )
    {

    }

    public function getName(): string
    {
        return 'set-student';
    }

    public function getDescription(): string
    {
        return "Enregistre l'étudiant comme étudiant";
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'member',
                'description' => "L'utilisateur ciblé",
                'type' => Option::USER,
                'required' => true,
            ],
            [
                'name' => 'channel',
                'description' => "Le salon de l'étudiant",
                'type' => Option::CHANNEL,
                'required' => false,
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $memberId = $interaction->data->options->get('name', 'member')->value;
        $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $memberId]);

        if ($student !== null && !$this->needToReworkStudent($student, $interaction->guild, $interaction->data->options->get('name', 'channel')?->value)) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'utilisateur est déjà défini comme un étudiant"));
        }

        if ($student === null) {
            $student = new Student();
            $student
                ->setMemberId($memberId);
        }

        $role = $this->studentBusiness->getStudentRole();

        if (null === $role) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Le role étudiant n'est pas connu. Utilisez la commande /set-student-role pour corriger cette erreur"));
        }

        /** @var Member $member */
        $member = $interaction->guild->members->offsetGet($memberId);

        if (null === $member) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("L'utilisateur '$memberId' n'est pas accessible"));
        }

        $student->setUsername($member->username);

        if (($channelId = $interaction->data->options->get('name', 'channel')?->value) !== null) {
            $channel = $this->channelBusiness->getChannel($channelId);

            if ($channel === null || !$channel->getBotPermissions()->view_channel || !$channel->getBotPermissions()->send_messages) {
                return $interaction->respondWithMessage(MessageBuilder::new()->setContent(
                    "Le salon <#$channelId> n'est pas accessible"
                ));
            }

            if ($channel->type !== Channel::TYPE_TEXT) {
                return $interaction->respondWithMessage(MessageBuilder::new()->setContent(
                    "Le salon <#$channelId> n'est pas textuel"
                ));
            }

            $deferred = new Deferred();
            $promise = $deferred->promise();
            $deferred->resolve($channel);
        } else {
            $member->addRole($role);
            $channel = $student->getChannelId() === null ? null : $this->channelBusiness->getStudentChannel($student);

            if ($channel === null || !$channel->getBotPermissions()->view_channel || !$channel->getBotPermissions()->send_messages) {
                $bitwisePermissions = Permission::ALL_PERMISSIONS['view_channel'] | Permission::TEXT_PERMISSIONS['send_messages'];
                $promise = $this->channelBusiness->createChannel($interaction->guild, $member->nick ?? $member->username, permissions: [
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
                ]);
            } else {
                $deferred = new Deferred();
                $promise = $deferred->promise();
                $deferred->resolve($channel);
            }
        }

        return $promise->then(function (Channel $channel) use ($interaction, $student) {
            $student->setChannelId($channel->id);

            $this->entityManager->persist($student);
            $this->entityManager->flush();

            $interaction->respondWithMessage(MessageBuilder::new()->setContent('Le member à bien été défini comme étudiant'));
        });
    }

    private function needToReworkStudent(Student $student, Guild $guild, ?string $channelId): bool
    {
        if ($channelId !== null && $student->getChannelId() !== $channelId) {
            return true;
        }

        if (!$this->studentBusiness->doesStudentHasRole($student, $guild)) {
            return true;
        }

        /** @var Channel $channel */
        $channel = $this->channelBusiness->getStudentChannel($student);

        if ($channel === null) {
            return true;
        }

        $permissions = $channel->getBotPermissions();

        if (!$permissions->view_channel || !$permissions->send_messages) {
            return true;
        }

        return false;
    }
}