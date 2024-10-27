<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Form\TicketDeleteType;
use App\Form\TicketType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backlog', name: 'backlog_')]
class BacklogController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    #[Route(name: 'index', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] int $assignee = null
    ): Response
    {
        $filter = [];
        if ($assignee !== null) $filter['assignee'] = $assignee;

        $tickets = $this->entityManager->getRepository(Ticket::class)->findBy($filter, ['id' => 'DESC']);
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('app/backlog.html.twig', [
            'tickets' => $tickets,
            'users' => $users
        ]);
    }

    #[Route('/ticket/new', name: 'addTicket', methods: ['GET', 'POST'])]
    public function addTicket(Request $request): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->author = $this->getUser();
            $this->entityManager->persist($ticket);
            $this->entityManager->flush();

            return $this->redirectToRoute('backlog_getTicket', ['ticket' => $ticket->id]);
        }

        return $this->render('app/addTicket.html.twig', ['form' => $form]);
    }

    #[Route('/ticket/{ticket}', name: 'getTicket', methods: ['GET', 'POST'])]
    public function getTicket(Request $request, Ticket $ticket): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        $deleteForm = $this->createForm(TicketDeleteType::class, $ticket);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted()) {
            if ($deleteForm->isValid()) {
                $this->entityManager->remove($ticket);
                $this->entityManager->flush();

                return $this->redirectToRoute('backlog_index');
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
        }


        return $this->render('app/ticket.html.twig', [
            'ticket' => $ticket,
            'deleteForm' => $deleteForm,
            'form' => $form,
        ]);
    }
}