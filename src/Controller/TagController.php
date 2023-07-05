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

class TagController extends AbstractController
{
    public function __construct(private TagRepository $tagRepository)
    {
    }

    #[Route('/tags', name: 'tag_index')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tagRepository->save($tag, true);
            $this->addFlash('sucess', 'New Tag has been created!');

            return $this->redirectToRoute('tag_index');
        }

        $tags = $this->tagRepository->findAll();
        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
            'tag_form' => $form->createView()
        ]);
    }

    #[Route('/tag/{name}', name: 'tag_show')]
    public function show(string $name): Response
    {
        $tag = $this->tagRepository->findOneByName($name);
        return $this->render('tag/show.html.twig', [
            'tag' => $tag
        ]);
    }

    #[Route('/tag/{id}/edit', name: 'tag_edit')]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(Tag $tag, Request $request): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tagRepository->save($tag, true);
            $this->addFlash('sucess', 'Tag was successfully updated!');

            return $this->redirectToRoute('tag_show', ['name' => $tag->getName()]);
        }

        return $this->render('tag/edit.html.twig', [
            'tag_form' => $form->createView()
        ]);
    }

    #[Route('/tag/{id}/delete', name: 'tag_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Tag $tag): Response
    {
        $this->tagRepository->remove($tag, true);
        $this->addFlash('sucess', 'Tag was successfully deleted!');
        return $this->redirectToRoute('tag_index');
    }
}
