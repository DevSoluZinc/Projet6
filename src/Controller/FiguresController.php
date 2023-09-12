<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Figures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\FiguresRepository;
use App\Entity\Comments;
use App\Form\CommentType;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Group;
use App\Repository\CommentsRepository;

class FiguresController extends AbstractController
{
    #[Route('/figures', name: 'app_figures')]
    public function index(): Response
    {
        return $this->render('figures/index.html.twig', [
            'controller_name' => 'FiguresController',
        ]);
    }

    #[Route('/figure/{id}', name: 'app_figure_detail')]
public function show(int $id, FiguresRepository $figuresRepository, Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
{
    $figure = $figuresRepository->findFigureById($id);

    if (!$figure) {
        throw $this->createNotFoundException('Figure not found');
    }

    // Récupérer le nom du groupe correspondant à figure.GroupId
    $groupName = '';
    $group = $entityManager->getRepository(Group::class)->find($figure->getGroupId());
    if ($group) {
        $groupName = $group->getName();
    }

    $comment = new Comments();
    $comment->setFigure($figure);

    $form = $this->createForm(CommentType::class, $comment);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setUser($this->getUser());

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été ajouté.');

        return $this->redirectToRoute('app_figure_detail', ['id' => $figure->getId()]);
    }

    $pagination = $paginator->paginate(
        $figure->getComments(),
        $request->query->getInt('page', 1),
        10
    );

    return $this->render('figures/detail.html.twig', [
        'figure' => $figure,
        'pagination' => $pagination,
        'commentForm' => $form->createView(),
        'groupName' => $groupName, // Transmettre le nom du groupe au modèle Twig
    ]);
}


    #[Route('/ajouter-figure', name: 'ajouter_figure')]
public function addFigure(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $figure = new Figures();
    $groupRepository = $entityManager->getRepository(Group::class);
    $groups = $groupRepository->findAll();
    $isFormValid = true;

    if ($request->isMethod('POST')) {
        $name = $request->request->get('name');
        $user_id = $request->request->get('user_id');
        $group = $request->request->get("groupId");
        $description = $request->request->get('description');
        $slug = $slugger->slug($name)->lower();

        if (empty($name) || empty($group) || empty($description)) {
            $this->addFlash('danger', 'Veuillez remplir tous les champs.');
            $isFormValid = false;
        }

        // Vérification de l'unicité du nom (vous devez ajuster cette partie en fonction de votre logique)
        $existingFigure = $entityManager->getRepository(Figures::class)->findOneBy(['name' => $name]);
        if ($existingFigure) {
            $this->addFlash('danger', 'Le nom de la figure existe déjà.');
            $isFormValid = false;
        }

        if ($isFormValid) {
            $figure->setCreatedAt(new \DateTime());
            $figure->setName($name);
            $figure->setDescription($description);
            $figure->setSlug($slug);
            $figure->setGroupId($group);
            $figure->setUserId($user_id);
            $uploadedIllustrationsPaths = [];

            $illustrations = $request->files->get('illustrations');

            if ($illustrations) {
                foreach ($illustrations as $illustration) {
                    if ($illustration instanceof UploadedFile) {
                        $newFilename = $slug . '-' . uniqid() . '.' . $illustration->guessExtension();
                        $illustration->move(
                            $this->getParameter('illustrations_directory'),
                            $newFilename
                        );

                        $uploadedIllustrationsPaths[] = $newFilename;
                    }
                }
            }

            $figure->setIllustrations($uploadedIllustrationsPaths);

            // Traitement des vidéos
            $videos = $request->request->get('videos');
            $figure->setMovie($videos);
            $entityManager->persist($figure);
            $entityManager->flush();

            $this->addFlash('success', 'La figure a été ajoutée avec succès.');

            return $this->redirectToRoute('app_default');
        }
    }

    return $this->render('figures/add_figure.html.twig',['groups' => $groups,]);
}


    #[Route('/modifier-figure/{id}', name: 'modifier_figure')]
public function editFigure(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, $id): Response
{
    $figureRepository = $entityManager->getRepository(Figures::class);
    $figure = $figureRepository->find($id);

    // Vérifiez si la figure existe
    if (!$figure) {
        throw $this->createNotFoundException('La figure n\'existe pas');
    }

    // Vous pouvez maintenant utiliser $figure pour pré-remplir le formulaire de modification.

    $groupRepository = $entityManager->getRepository(Group::class);
    $groups = $groupRepository->findAll();

    if ($request->isMethod('POST')) {
        // Traitez les données du formulaire de modification ici, en utilisant $figure pour mettre à jour les valeurs existantes.

        $name = $request->request->get('name');
        $group = $request->request->get("groupId");
        $description = $request->request->get('description');
        $slug = $slugger->slug($name)->lower();

        $figure->setName($name);
        $figure->setDescription($description);
        $figure->setSlug($slug);
        $figure->setGroupId($group);

        // Traitement des illustrations
        $anciennesIllustrations = $figure->getIllustrations();
        $uploadedIllustrationsPaths = [];

        $illustrations = $request->files->get('illustrations');

        if ($illustrations) {
            foreach ($illustrations as $illustration) {
                if ($illustration instanceof UploadedFile) {
                    $newFilename = $slug . '-' . uniqid() . '.' . $illustration->guessExtension();
                    $illustration->move(
                        $this->getParameter('illustrations_directory'),
                        $newFilename
                    );

                    $uploadedIllustrationsPaths[] = $newFilename;
                }
            }
        }

        $illustrations = array_merge($anciennesIllustrations, $uploadedIllustrationsPaths);
        $figure->setIllustrations($illustrations);
        // Traitement des vidéos
        $videos = $request->request->get('videos');
        $figure->setMovie($videos);

        $entityManager->flush();

        $this->addFlash('success', 'La figure a été modifiée avec succès.');

        return $this->redirectToRoute('app_default');
    }

    return $this->render('figures/edit_figure.html.twig', ['groups' => $groups, 'figure' => $figure]);
}

#[Route('/figure/supprimer/{id}', name: 'supprimer_figure')]
    public function supprimerFigure(int $id, FiguresRepository $figuresRepository, CommentsRepository $commentsRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérez la figure à supprimer en utilisant le repository
        $figure = $figuresRepository->find($id);

        if (!$figure) {
            throw $this->createNotFoundException('Figure not found');
        }

        // Récupérez tous les commentaires associés à cette figure
        $comments = $commentsRepository->findBy(['figure' => $figure]);

        // Supprimez d'abord les commentaires associés
        foreach ($comments as $comment) {
            $entityManager->remove($comment);
        }

        // Supprimez ensuite la figure elle-même
        $entityManager->remove($figure);
        $entityManager->flush();

        // Ajoutez un message flash pour indiquer que la figure et les commentaires ont été supprimés avec succès
        $this->addFlash('success', 'La figure et ses commentaires ont été supprimés avec succès.');

        // Redirigez l'utilisateur vers une page appropriée (par exemple, la liste des figures)
        return $this->redirectToRoute('app_default');
    }
}
