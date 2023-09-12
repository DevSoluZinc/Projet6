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

class RegistrationController extends AbstractController
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $pseudo = $request->request->get('pseudo');

            $user -> setPseudo($pseudo)
                ->setEmail($email)
                ->setRoles(['ROLE USER']);

            $hashPassword = $this->hasher->hashPassword(
                $user,
                $plainPassword
            );
            $user->setPassword($hashPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig');
    }
}
