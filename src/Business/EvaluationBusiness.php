<?php

namespace App\Business;

use App\Entity\Evaluation;

class EvaluationBusiness
{
    /** @param Evaluation[] $evaluations */
    public function getEvaluationsAverage(array $evaluations, int $averageMaxMark = 20): ?float
    {
        $weightedTotal = 0;
        $coefficientSum = 0;

        foreach ($evaluations as $evaluation) {
            $markPercentage = $evaluation->getMark() / $evaluation->getMaxMark();
            $weightedTotal += $markPercentage * $evaluation->getCoefficient();
            $coefficientSum += $evaluation->getCoefficient();
        }

        return ($coefficientSum > 0) ? round(($weightedTotal / $coefficientSum) * $averageMaxMark, 2) : null;
    }
}