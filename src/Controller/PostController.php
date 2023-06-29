<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    #[Route('/posts', name: 'post_index')]
    #[IsGranted("ROLE_CREATOR")]
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
    #[IsGranted("ROLE_CREATOR")]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $response = $this->handlePostForm($post, $request, 'The blog post was successfully saved!', $form);

        return $response ?? $this->render('post/create.html.twig', [
            'post_form' => $form->createView(),
        ]);
    }

    private function handlePostForm(
        Post $post,
        Request $request,
        string $flash,
        Form $form
    ): ?Response {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->get('tags')->getData() as $tag) {
                $post->addTag($tag);
                $tag->addPost($post);
            }

            $this->postRepository->save($post, true);
            $this->addFlash('sucess', $flash);

            return $this->redirectToRoute('post_show', ['slug' => $post->getSlug()]);
        }

        return null;
    }

    #[Route('/post/{slug}/edit', name: 'post_edit')]
    #[IsGranted("ROLE_CREATOR")]
    public function edit(string $slug, Request $request): Response
    {
        $post = $this->postRepository->findOneBySlug($slug);
        $form = $this->createForm(PostType::class, $post);
        $response = $this->handlePostForm($post, $request, 'The blog post was successfully updated!', $form);

        return $response ?? $this->render('post/edit.html.twig', [
            'post_form' => $form->createView(),
            'post' => $post
        ]);
    }

    #[Route('/post/{slug}', name: 'post_show')]
    public function show(string $slug): Response
    {
        $post = $this->postRepository->findOneBySlug($slug);
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/post/{slug}/delete', name: 'post_delete', methods: ["POST"])]
    #[IsGranted("ROLE_CREATOR")]
    public function delete(string $slug): Response
    {
        $post = $this->postRepository->findOneBySlug($slug);
        $this->postRepository->remove($post, true);
        return $this->redirectToRoute('homepage');
    }
}
