<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class StudentRepository extends EntityRepository
{

    public function findStudentsNotSeen(): array
    {
        $queryBuilder = $this->createQueryBuilder('student');

        $queryBuilder->where(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('student.tracking', ':tracking'),
                $queryBuilder->expr()->isNotNull('student.unseenMessageDateTime')
            )
        );

        $queryBuilder
            ->setParameter('tracking', true)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}