<?php

namespace App\MessageHandler\Message;

use App\Entity\Student;

readonly class SendEmailToParentsMessage
{
    public function __construct(
        private Student $student
    )
    {

    }

    public function getStudent(): Student
    {
        return $this->student;
    }
}