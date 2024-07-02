<?php

namespace App\MessageHandler\Handler;

use App\Business\ConfigBusiness;
use App\MessageHandler\Message\SendEmailToParentsMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SendEmailToParentsMessageHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ConfigBusiness $configBusiness,
        private readonly string $from,
    )
    {

    }

    public function __invoke(
        SendEmailToParentsMessage $message
    )
    {
        $email = new Email();
        $email
            ->from($this->from)
            ->to($message->getStudent()->getEmailAddress())
            ->subject($this->configBusiness->get('email_subject'))
            ->text($this->configBusiness->get('email_text'))
        ;
        $this->mailer->send($email);
    }
}