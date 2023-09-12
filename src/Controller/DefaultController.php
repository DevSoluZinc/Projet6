<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FiguresRepository;

class DefaultController extends AbstractController
{
    #[Route('/accueil', name: 'app_default')]
    public function index(FiguresRepository $figuresRepository): Response
    {

        $latestFigures = $figuresRepository->findLatestFigures(10);
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'latestFigures' => $latestFigures,
        ]);
    }
}
