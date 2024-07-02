<?php

namespace App\Business;


use App\Entity\Student;
use Discord\Discord;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;

class StudentBusiness
{
    public function __construct(
        private readonly ConfigBusiness         $configBusiness,
        private readonly Discord $discord
    )
    {

    }

    public function setStudentRole(Role $studentRole): void
    {
        $this->configBusiness->set('student_role', $studentRole->id);
    }

    public function getStudentRole(): ?Role
    {
        $roleId = $this->configBusiness->get('student_role');
        /** @var Guild $guild */
        foreach ($this->discord->guilds as $guild) {
            if ($role = $guild->roles->offsetGet($roleId)) {
                return $role;
            }
        }

        return null;
    }

    public function doesStudentHasRole(Student $student, Guild $guild): bool
    {
        /** @var Member $member */
        $member = $guild->members->offsetGet($student->getMemberId());

        if (null === $member) {
            return false;
        }

        return $member->roles->offsetGet($this->getStudentRole()->id) !== null;
    }
}