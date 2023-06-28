<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
class TagController extends AbstractController
{
    #[Route('/tags', name: 'tag_index')]
    public function index(TagRepository $tagRepository, Request $request): Response
    {
        $category = new Tag();
        $form = $this->createForm(TagType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tagRepository->save($category, true);
            $this->addFlash('sucess', 'New Category has been created!');

            return $this->redirectToRoute('tag_index');
        }

        $tags = $tagRepository->findAll();
        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
            'tag_form' => $form->createView()
        ]);
    }
}
