<?php

namespace App\MessageHandler\Message;

use App\Entity\Student;

readonly class AskEmailToSendMessage
{
    public function __construct(
        private Student $student,
    )
    {

    }

    public function getStudent(): Student
    {
        return $this->student;
    }
}