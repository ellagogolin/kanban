<?php

namespace App\Entity;

use App\Enum\TicketStatus;
use App\Repository\TicketLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketLogRepository::class)]
class TicketLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $loadChange = 0;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'log')]
    #[ORM\JoinColumn(name: 'ticket_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket = null;

    #[ORM\Column]
    private ?bool $previousShowOnKanban = null;

    #[ORM\Column]
    private ?bool $updatedShowOnKanban = null;

    #[ORM\Column(enumType: TicketStatus::class)]
    private ?TicketStatus $previousStatus = null;

    #[ORM\Column(enumType: TicketStatus::class)]
    private ?TicketStatus $updatedStatus = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLoadChange(): ?int
    {
        return $this->loadChange;
    }

    public function setLoadChange(?int $loadChange): static
    {
        $this->loadChange = $loadChange;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function isPreviousShowOnKanban(): ?bool
    {
        return $this->previousShowOnKanban;
    }

    public function setPreviousShowOnKanban(bool $previousShowOnKanban): static
    {
        $this->previousShowOnKanban = $previousShowOnKanban;

        return $this;
    }

    public function isUpdatedShowOnKanban(): ?bool
    {
        return $this->updatedShowOnKanban;
    }

    public function setUpdatedShowOnKanban(bool $updatedShowOnKanban): static
    {
        $this->updatedShowOnKanban = $updatedShowOnKanban;

        return $this;
    }

    public function getPreviousStatus(): ?TicketStatus
    {
        return $this->previousStatus;
    }

    public function setPreviousStatus(TicketStatus $previousStatus): static
    {
        $this->previousStatus = $previousStatus;

        return $this;
    }

    public function getUpdatedStatus(): ?TicketStatus
    {
        return $this->updatedStatus;
    }

    public function setUpdatedStatus(TicketStatus $updatedStatus): static
    {
        $this->updatedStatus = $updatedStatus;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }
}
