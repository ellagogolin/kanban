<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/feedback', name: 'app_feedback')]
class FeedbackController extends AbstractController
{
    public function __construct(
       private readonly EntityManagerInterface $entityManager
    ) {}


    #[Route(name: '_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $feedbacks = $this->entityManager->getRepository(Feedback::class)->findBy([], ['id' => 'DESC']);

        return $this->render('feedback/index.html.twig', [
            'controller_name' => 'FeedbackController',
            'feedbacks' => $feedbacks,
        ]);
    }
}
