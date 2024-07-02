<?php

namespace App\Business;

use Discord\Discord;
use React\Promise\PromiseInterface;

readonly class MessageBusiness
{
    public function __construct(
        private ChannelBusiness $channelBusiness
    )
    {

    }

    public function log($message): PromiseInterface
    {
        $channel = $this->channelBusiness->getOutputChannel();

        if (null === $channel) {
            throw new \Exception("Le salon de notifications n'est pas défini");
        }

        return $channel->sendMessage($message);
    }
}