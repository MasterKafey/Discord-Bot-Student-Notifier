<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'evaluations')]
    private ?Student $student = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, length: 255)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $mark = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $maxMark = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $coefficient = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $previewSent = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $notificationSent = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;
        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $startingDate): self
    {
        $this->date = $startingDate;
        return $this;
    }

    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(?float $mark): self
    {
        $this->mark = $mark;
        return $this;
    }

    public function getMaxMark(): ?float
    {
        return $this->maxMark;
    }

    public function setMaxMark(?float $maxMark): self
    {
        $this->maxMark = $maxMark;
        return $this;
    }

    public function getCoefficient(): ?int
    {
        return $this->coefficient;
    }

    public function setCoefficient(?int $coefficient): self
    {
        $this->coefficient = $coefficient;
        return $this;
    }

    public function isPreviewSent(): bool
    {
        return $this->previewSent;
    }

    public function setPreviewSent(bool $previewSent): self
    {
        $this->previewSent = $previewSent;
        return $this;
    }

    public function isNotificationSent(): bool
    {
        return $this->notificationSent;
    }

    public function setNotificationSent(bool $notificationSent): self
    {
        $this->notificationSent = $notificationSent;
        return $this;
    }
}