<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\Model\ChangePassword;
use App\Form\UserAccountType;
use App\Mail\MailMessage;
use App\Mail\MailService;
use App\Repository\ReservationRepository;
use App\Security\SupabaseUser;
use App\Security\UserResolver;
use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    public function __construct(private readonly MailService $mailService, private readonly EntityManagerInterface $entityManager,)
    {
    }
    #[Route('/', name: 'app_user_index')]
    public function index(
        ReservationRepository $reservationRepository,
        UserResolver $userResolver
    ): Response
    {
        $securityUser = $this->getUser();

        if (!$securityUser instanceof SupabaseUser) {
            throw $this->createAccessDeniedException();
        }
        $user = $userResolver->resolve($securityUser);

        $stats = $reservationRepository->getAccountStat($user);

        $upcomingReservations = $reservationRepository->getUpcomingReservations($user);
//        dd($user);


        return $this->render('pages/user/index.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'upcomingReservations' => $upcomingReservations
        ]);
    }

    #[Route('/profil', name: 'app_user_profile')]
    public function profile(
        Request $request,
        UserResolver $userResolver
    ): Response
    {
        $securityUser = $this->getUser();

        if (!$securityUser instanceof SupabaseUser) {
            throw $this->createAccessDeniedException();
        }

        $user = $userResolver->resolve($securityUser);

        $form = $this->createForm(UserAccountType::class, $user);
        $form->handleRequest($request);

        $changePassword = new ChangePassword();
        $passwordForm = $this->createForm(ChangePasswordType::class, $changePassword);
        $passwordForm->handleRequest($request);

//        $form = $this->createForm(UserAccountType::class, $user);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager->persist($user);
//            $entityManager->flush();
//            $this->addFlash('success', 'Profile modifié avec succès.');
//            return $this->redirectToRoute('app_user_profile');
//        }
//
//
//        // password
//        $changePassword = new ChangePassword();
//        $passwordForm = $this->createForm(ChangePasswordType::class, $changePassword);
//        $passwordForm->handleRequest($request);
//        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
//            $user->setPassword($passwordHasher->hashPassword($user, $changePassword->getNewPassword()));
//            $entityManager->persist($user);
//            $entityManager->flush();
//            $this->addFlash('success', 'Mot de passe modifié avec succès.');
//            return $this->redirectToRoute('app_user_profile');
//        }


        return $this->render('pages/user/profile.html.twig', [
            'user' => $user,
            'form' => $form,
            'passwordForm' => $passwordForm,
        ]);
    }

    #[Route('/reservations', name: 'app_user_reservations')]
    public function reservations(
        ReservationRepository $reservationRepository,
        Request $request,
        UserResolver $userResolver): Response
    {

        $filter = $request->query->get('filter', 'all');
        $page = $request->query->getInt('page', 1);

        $securityUser = $this->getUser();

        if (!$securityUser instanceof SupabaseUser) {
            throw $this->createAccessDeniedException();
        }

        $user = $userResolver->resolve($securityUser);

        $result = $reservationRepository->findByUserWithFilters(
            $user,
            $filter,
            $page
        );

        return $this->render('pages/user/reservations.html.twig', [
            'reservations' => $result['items'],
            'pagination' => $result['pagination'],
            'filter' => $filter,
        ]);
    }

    #[Route('/preferences', name: 'app_user_preferences')]
    public function preferences(): Response
    {
        return $this->render('pages/user/preferences.html.twig');
    }

    #[Route('/delete/{id}', name: 'app_user_delete')]
    #[ParamDecryptor(['id'])]
    public function delete(Request $request, User $user): Response
    {
        $submittedToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete-user' . $user->getId(), $submittedToken)) {
            $this->entityManager->remove($user);
            $this->mailService->send(MailMessage::accountDeletion($user));
            $this->entityManager->flush();
            $this->addFlash('success_user', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_home');
    }
}
