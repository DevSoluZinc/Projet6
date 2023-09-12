<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\ResetPasswordToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid as SymfonyUid;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\EmailService;

class ResetPasswordController extends AbstractController
{
    private $entityManager;
    private $emailService;
    private UserPasswordHasherInterface $hasher;

    public function __construct(EntityManagerInterface $entityManager,EmailService $emailService,UserPasswordHasherInterface $hasher)
    {
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->hasher = $hasher;

    }
    #[Route('/reset-password-demande', name: 'app_reset_password_demande')]
    public function index(Request $request): Response
    {
        return $this->render('reset_password/resetPasswordDemande.html.twig', [
            'controller_name' => 'ResetPasswordController',
        ]);
    }
    #[Route('/reset-password-request', name: 'app_reset_password_request')]
    public function request(Request $request): Response
    {
        $username = $request->request->get('username');
        // Recherchez l'utilisateur par pseudonyme et récupérez son adresse e-mail.
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['Pseudo' => $username]);

        if (!$user) {
            return new Response('Utilisateur inconnu', Response::HTTP_NOT_FOUND);
        } else {
            // Générez un jeton unique.
            $token = Uuid::v4()->toRfc4122();

            // Stockez le jeton en base de données avec une expiration (par exemple, 1 heure).
            $resetPasswordToken = new ResetPasswordToken();
            $resetPasswordToken->setUser($user);
            $resetPasswordToken->setToken($token);
            $resetPasswordToken->setExpiresAt(new \DateTime('+1 hour'));
            $resetPasswordLink = $this->generateUrl('app_reset_password_intermediate', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->entityManager->persist($resetPasswordToken);
            $this->entityManager->flush();

            // Créez et envoyez l'e-mail de réinitialisation.
        $subject = 'Réinitialisation de mot de passe';
        $message = 'Cliquez sur le lien suivant pour réinitialiser votre mot de passe : ' . $resetPasswordLink;
        

        // Utilisez le service EmailService pour envoyer l'e-mail
        $this->emailService->sendEmail($user->getEmail(), $subject, $message);
        $this->addFlash('success', 'L\'e-mail de réinitialisation a été envoyé avec succès.');
            return $this->redirectToRoute('app_default');
        }
    }
    #[Route('/reset-password-reset/{token}', name: 'app_reset_password_reset')]
    public function reset(Request $request,  UserPasswordHasherInterface $passwordHasher, $token,EntityManagerInterface $entityManager): Response
    {
        // Récupérez le token de la route
        $tokenEntity = $this->entityManager->getRepository(ResetPasswordToken::class)->findOneBy(['token' => $token]);

        if (!$tokenEntity) {
            // Gérer le cas où le token n'est pas trouvé ou est invalide.
            // Vous pouvez rediriger vers une page d'erreur ou afficher un message d'erreur.
            return $this->render('reset_password/error.html.twig', [
                'message' => 'Token invalide ou expiré.'
            ]);
        }
        // Récupérez l'utilisateur associé au jeton.
        $user = $tokenEntity->getUser();
        if (!$user) {
            // Gérer le cas où l'utilisateur n'est pas trouvé.
            return $this->render('reset_password/error.html.twig', [
                'message' => 'Utilisateur introuvable.'
            ]);
        }

        if ($request->isMethod('POST')) {
            $plainPassword = $request->request->get('password');
            if ($plainPassword) {
                $hashedPassword = $this->hasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }
            $entityManager->remove($tokenEntity);
            $entityManager->flush();
            dd($entityManager);
            return $this->redirectToRoute('app_default');
        }
    }
    #[Route('/reset-password-intermediate/{token}', name: 'app_reset_password_intermediate')]
public function intermediate(Request $request, $token): Response
{
    $tokenEntity = $this->entityManager->getRepository(ResetPasswordToken::class)->findOneBy(['token' => $token]);

    if (!$tokenEntity) {
        return $this->render('reset_password/error.html.twig', [
            'message' => 'Token invalide ou expiré.'
        ]);
    }
    $user = $tokenEntity->getUser();
    if (!$user) {
        return $this->render('reset_password/error.html.twig', [
            'message' => 'Utilisateur introuvable.'
        ]);
    }
    return $this->render('reset_password/resetPassword.html.twig', [
        'token' => $token, 
    ]);
}

}