<?php

namespace App\Repository;

use App\Entity\Evaluation;
use App\Entity\Student;
use Doctrine\ORM\EntityRepository;

class EvaluationRepository extends EntityRepository
{

    public function search(Student $student, ?\DateTime $startingDate = null, ?\DateTime $endingDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('evaluation');

        $andX = [
            $queryBuilder->expr()->eq('evaluation.student', ':student')
        ];
        $queryBuilder->setParameter('student', $student);

        if (null !== $startingDate) {
            $andX[] = $queryBuilder->expr()->gte('evaluation.date', ':starting_date');
            $queryBuilder->setParameter('starting_date', $startingDate);
        }

        if (null !== $endingDate) {
            $andX[] = $queryBuilder->expr()->lte('evaluation.date', ':ending_date');
            $queryBuilder->setParameter('ending_date', $endingDate);
        }

        $queryBuilder->where(
            $queryBuilder->expr()->andX(...$andX)
        );

        return $queryBuilder->getQuery()->getResult();
    }

    /** @return Evaluation[] */
    public function getPreviewToSend(\DateInterval $interval): array
    {
        $queryBuilder = $this->createQueryBuilder('evaluation');

        $queryBuilder->where(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('evaluation.previewSent', ':preview_sent'),
                $queryBuilder->expr()->lte('evaluation.date', ':date'),
            )
        );

        $atLeast = (new \DateTime())->add($interval);

        $queryBuilder
            ->setParameter('preview_sent', false)
            ->setParameter('date', $atLeast)
        ;
        return $queryBuilder->getQuery()->getResult();
    }

    /** @return Evaluation[] */
    public function getNotNotifyEvaluations(): array
    {
        $queryBuilder = $this->createQueryBuilder('evaluation');

        $queryBuilder->where(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('evaluation.notificationSent', ':notification_sent'),
                $queryBuilder->expr()->lte('evaluation.date', ':starting_date'),
            )
        );

        $startingDate = (new \DateTime());

        $queryBuilder
            ->setParameter('notification_sent', false)
            ->setParameter('starting_date', $startingDate)
        ;
        return $queryBuilder->getQuery()->getResult();
    }
}