<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Ticket;
use App\Entity\TicketLog;
use App\Entity\User;
use App\Enum\TicketStatus;
use App\Form\FeedbackType;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/', name: 'kanban_')]
class KanbanController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    { }

    #[Route(name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        #[MapQueryParameter] int $assignee = null,
        ChartBuilderInterface $chartBuilder
    ): Response
    {
        $filter = ['displayOnKanban' => true];
        if ($assignee !== null) $filter['assignee'] = $assignee;

        $tickets = $this->entityManager->getRepository(Ticket::class)->findBy($filter, ['status' => 'ASC']);
        $ticketsByStatus = [];
        $ticketsByStatus[TicketStatus::READY->value] = [];
        $ticketsByStatus[TicketStatus::IN_PROGRESS->value] = [];
        $ticketsByStatus[TicketStatus::DONE->value] = [];
        foreach ($tickets as $ticket) {
            $ticketsByStatus[$ticket->status->value][] = $ticket;
        }

        $feedback = new Feedback();
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $feedback->setUser($this->getUser());
            $this->entityManager->persist($feedback);
            $this->entityManager->flush();
            $feedback = new Feedback();
            $form = $this->createForm(FeedbackType::class, $feedback);
        }

        $users = $this->entityManager->getRepository(User::class)->findAll();

        $burndownResult = $this->entityManager->getRepository(TicketLog::class)->getBurndownResult();
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => array_map(static function ($item) {
                return date_format($item['date'], 'd.m.Y');
            }, $burndownResult),
            'datasets' => [
                [
                    'label' => 'Burndown',
                    'borderColor' => '#2f972f',
                    'data' => array_map(static function ($item) {
                        return $item['runningTotal'];
                    }, $burndownResult),
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'min' => 0,
                    'suggestedMin' => 0,
                    'suggestedMax' => 10,
                ],
            ],
        ]);


        return $this->render('app/kanban.html.twig', [
            'form' => $form,
            'ticketsByStatus' => $ticketsByStatus,
            'users' => $users,
            'chart' => $chart
        ]);
    }
}