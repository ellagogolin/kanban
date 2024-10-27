<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\TicketStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Ticket $ticket */
        $ticket = $builder->getData();
        $builder
            ->add('title')
            ->add('description')
            ->add('displayOnKanban')
            ->add('priority', ChoiceType::class, [
                'choices'  => [
                    'None' => 0,
                    'Low' => 1,
                    'Medium' => 2,
                    'High' => 3,
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => $ticket->id
                    ? [
                        'Ready' => TicketStatus::READY,
                        'In Progress' => TicketStatus::IN_PROGRESS,
                        'Done' => TicketStatus::DONE,
                    ]
                    : [
                        'Ready' => TicketStatus::READY,
                        'In Progress' => TicketStatus::IN_PROGRESS,
                    ]
            ])
            ->add('assignee', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
