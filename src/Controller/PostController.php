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
    public function __construct(private PostRepository $postRepository)
    {
    }

    #[Route('/posts', name: 'post_index')]
    public function index(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->postRepository->getPostPaginator($offset);

        return $this->render('post/index.html.twig', [
            'posts' => $paginator,
            'previous' => $offset - PostRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + PostRepository::PAGINATOR_PER_PAGE),
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

            return $this->redirectToRoute('post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/create.html.twig', [
            'post_form' => $form
        ]);
    }

    #[Route('/post/{slug}', name: 'post_show')]
    public function show(string $slug): Response
    {
        $post = $this->postRepository->findOneBy(['slug' => $slug]);
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/post/{slug}/edit', name: 'post_edit')]
    public function edit(string $slug, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = $this->postRepository->findOneBy(['slug' => $slug]);
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('sucess', 'The blog post was successfully updated!');

            return $this->redirectToRoute('post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/edit.html.twig', [
            'post_form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/post/{slug}/delete', name: 'post_delete')]
    public function delete(string $slug): Response
    {
        $post = $this->postRepository->findOneBy(['slug' => $slug]);
        $this->postRepository->remove($post, true);
        return $this->redirectToRoute('homepage');
    }
}