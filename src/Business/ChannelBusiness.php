<?php

namespace App\Business;

use App\Entity\Student;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Part;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;

readonly class ChannelBusiness
{
    public function __construct(
        private ConfigBusiness         $configBusiness,
        private EntityManagerInterface $entityManager,
        private Discord                $discord
    )
    {

    }

    public function getChannel(string $channelId): ?Channel
    {
        return $this->discord->getChannel($channelId);
    }

    public function setOutputChannel(Channel $channel): void
    {
        $this->configBusiness->set('output_channel', $channel->id);
    }

    public function getOutputChannel(): ?Channel
    {
        $channelId = $this->configBusiness->get('output_channel');

        if (null === $channelId) {
            return null;
        }

        return $this->getChannel($this->configBusiness->get('output_channel'));
    }

    public function getStudentChannel(Student|string $student): ?Channel
    {
        if (!($student instanceof Student)) {
            $student = $this->entityManager->getRepository(Student::class)->findOneBy(['memberId' => $student]);
        }

        return $this->getChannel($student->getChannelId());
    }


    public function createChannel(Guild $guild, string $name, string $channelType = Channel::TYPE_TEXT, array $permissions = []): PromiseInterface
    {
        $data = [
            'name' => $name,
            'type' => $channelType,
            'permission_overwrites' => $permissions
        ];

        $categoryId = $this->configBusiness->get('category_student_channels');

        if (null !== $categoryId) {
            /** @var Channel $category */
            $category = $guild->channels->offsetGet($categoryId);
            if (null !== $category) {
                $data['parent_id'] = $category->id;
            }
        }

        $channel = $guild->channels->create($data);
        return $guild->channels->save($channel);
    }
}