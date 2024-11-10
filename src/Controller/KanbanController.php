<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Ticket;
use App\Entity\TicketLog;
use App\Entity\User;
use App\Enum\TicketStatus;
use App\Form\FeedbackType;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
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
        $date = new \DateTimeImmutable();
        $weekNumber = $date->format("W");
        $sprintInterval = DateInterval::createFromDateString(($weekNumber %2 === 0 ? 6 : 13)-date('w').' days');
        $end = new DateTime();
        $end->add($sprintInterval);
        $startInterval = DateInterval::createFromDateString('14 days');
        $start = clone $end;
        $start->sub($startInterval);

        if (count($burndownResult) > 0) {
            $lastItem = end($burndownResult);


            if ($lastItem['date']->format('Y-m-d') !== $date->format('Y-m-d')) {
                $burndownResult[] = ['date' => $date, 'runningTotal' => $lastItem['runningTotal']];
            }
        }

        $runningTotalOnStart = 0;

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'borderColor' => '#2f972f',
                    'data' => array_map(static function ($item) {
                        return ['x' => $item['date']->format('Y-m-d'), 'y' => $item['runningTotal']];
                    }, $burndownResult),
                ],
                [
                    'label' => 'Burndown',
                    'borderColor' => '#ffffff',
                    'data' => [
                        ['x' => '2024-10-31', 'y' => 7],
                        ['x' => $end->format('Y-m-d'), 'y' => 0],
                    ],
                    'labels' => [$start->format('Y-m-d'), $end->format('Y-m-d')],
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
            'chart' => $chart,
            'runningTotalOnStart' => $runningTotalOnStart,
            'start' => $start,
            'end' => $end,
        ]);
    }
}