<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'post_index')]
    public function index(PostRepository $postRepository, Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $postRepository->getPostPaginator($offset);

        return $this->render('post/index.html.twig', [
            'posts' => $paginator,
            'previous' => $offset - PostRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + PostRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/post/{id<\d+>}', name: 'post_show')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/post/create', name: 'post_create')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('sucess', 'The blog post was successfully saved!');

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/create.html.twig', [
            'post_form' => $form
        ]);
    }

    #[Route('/post/{id<\d+>}/edit', name: 'post_edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('sucess', 'The blog post was successfully updated!');

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'post_form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/post/{id<\d+>}/delete', name: 'post_delete')]
    public function delete(Post $post, PostRepository $postRepository): Response
    {
        $postRepository->remove($post, true);
        return $this->redirectToRoute('homepage');
    }
}
