<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    public function index(UserRepository $userRepository): Response
    {
        // Récupère tous les utilisateurs
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/add-test', name: 'user_add_test')]
    public function addTest(EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setId(1);
        $user->setNom('Snoop');
        $user->setPrenom('Dogg');
        $user->setEmail('snoop.dogg@test.com');
        $user->setAdresse('12 rue de la gare');
        $user->setTel('06 00 00 00 00');

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_list');
    }

}
