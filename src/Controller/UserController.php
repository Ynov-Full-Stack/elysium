<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Form\Model\ChangePassword;
use App\Form\UserAccountType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();

        $stats = $reservationRepository->getAccountStat($user);

        $upcomingReservations = $reservationRepository->getUpcomingReservations($user);


        return $this->render('pages/user/index.html.twig', [
            'stats' => $stats,
            'upcomingReservations' => $upcomingReservations
        ]);
    }

    #[Route('/profil', name: 'app_user_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // user profil
        $user = $this->getUser();
        $form = $this->createForm(UserAccountType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profile modifié avec succès.');
            return $this->redirectToRoute('app_user_profile');
        }

        // password
        $changePassword = new ChangePassword();
        $passwordForm = $this->createForm(ChangePasswordType::class, $changePassword);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $changePassword->getNewPassword()));
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Mot de passe modifié avec succès.');
            return $this->redirectToRoute('app_user_profile');
        }


        return $this->render('pages/user/profile.html.twig', [
            'form' => $form->createView(),
            'passwordForm' => $passwordForm->createView()
        ]);
    }

    #[Route('/reservations', name: 'app_user_reservations')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        // add annulation

        $reservations = $reservationRepository->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);

        $pagination = [
            'currentPage' => 1,
            'totalPages' => 1
        ];

        return $this->render('pages/user/reservations.html.twig', [
            'reservations' => $reservations,
            'pagination' => $pagination
        ]);
    }

    #[Route('/preferences', name: 'app_user_preferences')]
    public function preferences(): Response
    {
        // inside add delete account, newsletter,
        return $this->render('pages/user/preferences.html.twig');
    }
}
