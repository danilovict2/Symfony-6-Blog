<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    public function __construct(
        private PostRepository $postRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus
    ) {
    }

    #[Route('/posts', name: 'post_index')]
    #[IsGranted("ROLE_ADMIN")]
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
    #[IsGranted("ROLE_ADMIN")]
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
            $this->postRepository->save($post, true);
            $this->addFlash('sucess', $flash);

            return $this->redirectToRoute('post_show', ['slug' => $post->getSlug()]);
        }

        return null;
    }

    #[Route('/post/{slug}/edit', name: 'post_edit')]
    #[IsGranted("ROLE_ADMIN")]
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
    public function show(string $slug, Request $request, CommentRepository $commentRepository): Response
    {
        $post = $this->postRepository->findOneBySlug($slug);
        $postComments = $commentRepository->getPostComments($post);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setPoster($this->getUser());

            $commentRepository->save($comment, true);
            $this->addFlash('success', 'Comment was added, after review it will be posted!');

            $this->dispatchCommentMessage($comment, $request);

            return $this->redirectToRoute('post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $postComments,
            'comment_form' => $form->createView()
        ]);
    }

    private function dispatchCommentMessage(Comment $comment, Request $request): void
    {
        $context = [
            'user_ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('user-agent'),
            'referrer' => $request->headers->get('referer'),
            'permalink' => $request->getUri(),
        ];
        $this->bus->dispatch(new CommentMessage($comment->getId(), $context));
    }

    #[Route('/post/{slug}/delete', name: 'post_delete', methods: ["POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(string $slug): Response
    {
        $post = $this->postRepository->findOneBySlug($slug);
        $this->postRepository->remove($post, true);
        return $this->redirectToRoute('homepage');
    }
}
