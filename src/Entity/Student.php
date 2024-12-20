<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, unique: true)]
    private ?string $memberId = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $username = null;

    #[ORM\Column(type: Types::DATEINTERVAL)]
    private ?\DateInterval $intervalNotification;

    #[ORM\Column(type: Types::DATEINTERVAL)]
    private ?\DateInterval $intervalInactivity;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $lastActivityDateTime;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastNotification = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $notificationBeforeMail = 1;

    #[ORM\Column(type: Types::INTEGER)]
    private int $currentNotificationBeforeMail = 1;

    #[ORM\Column(type: Types::STRING)]
    private ?string $channelId = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $tracking = true;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $emailAddress = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastTeacherNotification = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $unseenMessageDateTime = null;

    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'student', cascade: ['persist', 'remove'])]
    private Collection $evaluations;

    public function __construct()
    {
        $this->lastActivityDateTime = new \DateTime();
        $this->evaluations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMemberId(): ?string
    {
        return $this->memberId;
    }

    public function setMemberId(?string $memberId): self
    {
        $this->memberId = $memberId;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getIntervalNotification(): ?\DateInterval
    {
        return $this->intervalNotification;
    }

    public function setIntervalNotification(?\DateInterval $intervalNotification): self
    {
        $this->intervalNotification = $intervalNotification;
        return $this;
    }

    public function getIntervalInactivity(): ?\DateInterval
    {
        return $this->intervalInactivity;
    }

    public function setIntervalInactivity(?\DateInterval $intervalInactivity): self
    {
        $this->intervalInactivity = $intervalInactivity;
        return $this;
    }

    public function getLastActivityDateTime(): ?\DateTime
    {
        return $this->lastActivityDateTime;
    }

    public function setLastActivityDateTime(?\DateTime $lastActivityDateTime): self
    {
        $this->lastActivityDateTime = $lastActivityDateTime;
        return $this;
    }

    public function getLastNotification(): ?\DateTime
    {
        return $this->lastNotification;
    }

    public function setLastNotification(?\DateTime $lastNotification): self
    {
        $this->lastNotification = $lastNotification;
        return $this;
    }

    public function getNotificationBeforeMail(): int
    {
        return $this->notificationBeforeMail;
    }

    public function setNotificationBeforeMail(int $notificationBeforeMail): self
    {
        $this->notificationBeforeMail = $notificationBeforeMail;
        return $this;
    }

    public function getCurrentNotificationBeforeMail(): int
    {
        return $this->currentNotificationBeforeMail;
    }

    public function setCurrentNotificationBeforeMail(int $currentNotificationBeforeMail): self
    {
        $this->currentNotificationBeforeMail = $currentNotificationBeforeMail;
        return $this;
    }

    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    public function setChannelId(?string $channelId): self
    {
        $this->channelId = $channelId;
        return $this;
    }

    public function isTracking(): bool
    {
        return $this->tracking;
    }

    public function setTracking(bool $tracking): self
    {
        $this->tracking = $tracking;
        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function getLastTeacherNotification(): ?\DateTime
    {
        return $this->lastTeacherNotification;
    }

    public function setLastTeacherNotification(?\DateTime $lastTeacherNotification): self
    {
        $this->lastTeacherNotification = $lastTeacherNotification;
        return $this;
    }

    public function getUnseenMessageDateTime(): ?\DateTime
    {
        return $this->unseenMessageDateTime;
    }

    public function setUnseenMessageDateTime(?\DateTime $unseenMessageDateTime): self
    {
        $this->unseenMessageDateTime = $unseenMessageDateTime;
        return $this;
    }

    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function setEvaluations(Collection $evaluations): self
    {
        $this->evaluations = $evaluations;
        return $this;
    }
}