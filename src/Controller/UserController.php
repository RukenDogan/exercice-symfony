<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Possession;
use App\Service\UserService;


class UserController extends AbstractController
{
#[Route('/users', name: 'user_list')]
public function index(
    UserRepository $userRepository,
    EntityManagerInterface $entityManager,
    UserService $userService
): Response {
    // Récupération de tous les utilisateurs
    $users = $userRepository->findAll();

    // Données par défaut
    $usersData = [
        'Snoop' => ['prenom'=>'Dogg', 'email'=>'snoop.dogg@test.com', 'adresse'=>'12 rue de la gare', 'tel'=>'06 00 00 00 00', 'birthDate'=>'1971-10-20'],
        'Sade'  => ['prenom'=>'Adu',  'email'=>'sade@test.com',      'adresse'=>'15 rue du jazz',     'tel'=>'06 11 22 33 44', 'birthDate'=>'1959-01-16'],
        'Prince'=> ['prenom'=>'Rogers','email'=>'prince@test.com',   'adresse'=>'21 rue de la funk',  'tel'=>'06 55 66 77 88', 'birthDate'=>'1958-06-07'],
    ];

    foreach ($usersData as $nom => $data) {
        // Vérifie si l'utilisateur existe déjà
        $user = $userRepository->findOneBy(['nom' => $nom]);
        if (!$user) {
            // Création si n'existe pas
            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($data['prenom']);
            $user->setEmail($data['email']);
            $user->setAdresse($data['adresse']);
            $user->setTel($data['tel']);
            $user->setBirthDate(new \DateTime($data['birthDate']));
            $entityManager->persist($user);
        } else {
            // Si existe, on met à jour la date de naissance si elle est vide
            if (!$user->getBirthDate()) {
                $user->setBirthDate(new \DateTime($data['birthDate']));
                $entityManager->persist($user);
            }
        }
    }

    $entityManager->flush();

    // Recalcul de l'âge
    $users = $userRepository->findAll();
    $usersWithAge = [];
    foreach ($users as $user) {
        $usersWithAge[] = [
            'user' => $user,
            'age' => $userService->calculateAge($user),
        ];
    }

    return $this->render('user/index.html.twig', [
        'usersWithAge' => $usersWithAge,
    ]);
}


#[Route('/users/{id}/delete', name: 'user_delete', methods: ['POST'])]
public function delete(User $user, EntityManagerInterface $entityManager, Request $request): Response
{
    if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
        $entityManager->remove($user);
        $entityManager->flush();
    }

    return $this->redirectToRoute('user_list');
}


#[Route('/users/{id}', name: 'user_show')]
public function show(User $user): Response
{
    // On récupère les possessions grâce à la relation
    $possessions = $user->getPossessions();

    return $this->render('user/show.html.twig', [
        'user' => $user,
        'possessions' => $possessions,
    ]);
}

#[Route('/users/add-possessions', name: 'add_sample_possessions')]
public function addSamplePossessions(EntityManagerInterface $em, UserRepository $repo): Response
{
    $users = $repo->findAll();

    foreach ($users as $user) {

        $p1 = new Possession();
        $p1->setNom('Guitare')
            ->setValeur(1500)
            ->setType('Instrument')
            ->setDescription('Guitare électrique de collection')
            ->setUser($user);

        $p2 = new Possession();
        $p2->setNom('Album Vinyl')
            ->setValeur(80)
            ->setType('Musique')
            ->setDescription('Vinyle original pressage')
            ->setUser($user);

        $em->persist($p1);
        $em->persist($p2);
    }

    $em->flush();

    return $this->redirectToRoute('user_list');
}



}