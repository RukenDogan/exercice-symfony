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

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
public function index(UserRepository $userRepository, EntityManagerInterface $entityManager): Response
{
    $users = $userRepository->findAll();

    if (empty($users)) {
        $usersData = [
            ['nom'=>'Snoop', 'prenom'=>'Dogg', 'email'=>'snoop.dogg@test.com', 'adresse'=>'12 rue de la gare', 'tel'=>'06 00 00 00 00'],
            ['nom'=>'Sade', 'prenom'=>'Adu', 'email'=>'sade@test.com', 'adresse'=>'15 rue du jazz', 'tel'=>'06 11 22 33 44'],
            ['nom'=>'Prince', 'prenom'=>'Rogers', 'email'=>'prince@test.com', 'adresse'=>'21 rue de la funk', 'tel'=>'06 55 66 77 88'],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $user->setEmail($data['email']);
            $user->setAdresse($data['adresse']);
            $user->setTel($data['tel']);
            $entityManager->persist($user);
        }

        $entityManager->flush();

        // Recharge les utilisateurs après insertion
        $users = $userRepository->findAll();
    }
    

    return $this->render('user/index.html.twig', [
        'users' => $users,
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