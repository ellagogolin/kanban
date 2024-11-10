<?php

namespace App\Entity;

use App\Enum\TicketStatus;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 70)]
    #[NotBlank]
    #[Length(min: 3, max: 70)]
    public ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[NotBlank]
    #[Length(min: 20)]
    public ?string $description = null;

    #[ORM\Column]
    public ?bool $displayOnKanban = false;

    #[ORM\ManyToOne(inversedBy: 'authoredTickets')]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'assignedTickets')]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $assignee = null;

    #[ORM\Column(length: 15, enumType: TicketStatus::class)]
    public TicketStatus $status = TicketStatus::READY;

    #[ORM\Column(type:Types::INTEGER)]
    #[Range(min: 0, max: 3)]
    public ?int $priority = null;

    /**
     * @var Collection<int, TicketLog>
     */
    #[ORM\OneToMany(targetEntity: TicketLog::class, mappedBy: 'ticket')]
    private Collection $log;

    public function __construct()
    {
        $this->log = new ArrayCollection();
    }

    /**
     * @return Collection<int, TicketLog>
     */
    public function getLog(): Collection
    {
        return $this->log;
    }

    public function addLog(TicketLog $log): static
    {
        if (!$this->log->contains($log)) {
            $this->log->add($log);
            $log->setTicket($this);
        }

        return $this;
    }

    public function removeLog(TicketLog $log): static
    {
        if ($this->log->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getTicket() === $this) {
                $log->setTicket(null);
            }
        }

        return $this;
    }
}
