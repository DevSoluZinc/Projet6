<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class UsersController extends AbstractController
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    #[Route('/users', name: 'app_users')]
    public function index(): Response
    {
        return $this->render('users/index.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }

    #[Route('/edit-profile', name: 'app_user_edit')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $pseudo = $request->request->get('pseudo');
            $user->setPseudo($pseudo);
            $user->setEmail($email);

            if ($plainPassword) {
                $hashedPassword = $this->hasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }

            // Gérer le téléchargement et la mise à jour de l'image ici
            $uploadedImage = $request->files->get('image');
            if ($uploadedImage instanceof UploadedFile) {
                $newFilename = $user->getPseudo() . '-' . uniqid() . '.' . $uploadedImage->guessExtension();
                $uploadedImage->move(
                    $this->getParameter('user_images_directory'),
                    $newFilename
                );

                // Mettre à jour le chemin de l'image de l'utilisateur
                $user->setImage('User/' . $newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_default');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
        ]);
    }

}
