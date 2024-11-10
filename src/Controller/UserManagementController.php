<?php

namespace App\Controller;


use App\Entity\Feedback;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\UserDeleteType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users', name: 'userManagement_')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
   public function __construct(
       private readonly EntityManagerInterface $entityManager,
   ){}
    #[Route(name:'index', methods: ['GET'])]
    public function index(): Response
    {
      $users = $this->entityManager->getRepository(User::class)->findAll();

      return $this->render('userManagement/index.html.twig', [
          'users' => $users,
      ]);
    }
    #[Route('/{user}', name: 'editUser', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $deleteForm = $this->createForm(UserDeleteType::class, $user);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted()) {
            if ($deleteForm->isValid()) {
                /** @var Ticket $ticket */
                foreach ($user->getAssignedTickets() as $ticket) {
                    $ticket->assignee = null;
                }
                /** @var Ticket $ticket */
                foreach ($user->getAuthoredTickets() as $ticket) {
                    $ticket->author = null;
                }
                /** @var Feedback $feedback */
                foreach ($user->getFeedback() as $feedback) {
                    $feedback->setUser(null);
                }
                $this->entityManager->flush();
                $this->entityManager->remove($user);
                $this->entityManager->flush();

                return $this->redirectToRoute('userManagement_index');
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $isAdmin = (bool) $form->get('isAdmin')->getData();
            $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : []);
            $this->entityManager->flush();
        }

        return $this->render('userManagement/userEdit.html.twig', [
            'user' => $user,
            'deleteForm' => $deleteForm,
            'form' => $form,
        ]);
    }
}
