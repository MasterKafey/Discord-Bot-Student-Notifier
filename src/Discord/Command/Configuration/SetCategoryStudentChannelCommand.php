<?php

namespace App\Discord\Command\Configuration;

use App\Business\ChannelBusiness;
use App\Business\ConfigBusiness;
use App\Discord\Command\AbstractDiscordCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use React\Promise\PromiseInterface;

class SetCategoryStudentChannelCommand extends AbstractDiscordCommand
{
    public function __construct(
        private readonly ChannelBusiness $channelBusiness,
        private readonly ConfigBusiness $configBusiness
    )
    {

    }

    public function getName(): string
    {
        return 'set-category-student-channel';
    }

    public function getDescription(): string
    {
        return "Défini la categorie qui contiendra les futurs salons d'étudiants";
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'category',
                'type' => Option::CHANNEL,
                'required' => true,
                'description' => "La categorie cible",
            ]
        ];
    }

    public function execute(Interaction $interaction): ?PromiseInterface
    {
        $channelId = $interaction->data->options->get('name', 'category')->value;

        /** @var Channel $channel */
        $channel = $this->channelBusiness->getChannel($channelId);

        if (!($channel instanceof Channel)) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Ce salon n'est pas accessible"));
        }

        if ($channel->type !== Channel::TYPE_CATEGORY) {
            return $interaction->respondWithMessage(MessageBuilder::new()->setContent("Ce salon n'est pas une category"));
        }

        $this->configBusiness->set('category_student_channels', $channel->id);

        return $interaction->respondWithMessage(MessageBuilder::new()->setContent("La category <#$channel->id> est défini pour contenir les futurs salons étudiants"));
    }
}