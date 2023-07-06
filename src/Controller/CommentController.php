<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    #[IsGranted("ROLE_USER")]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $comment->getPoster()) {
            return $this->redirectToRoute('homepage');
        }

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comment was successfully updated!');
            return $this->redirectToRoute('post_show', ['slug' => $comment->getPost()->getSlug()]);
        }

        return $this->render('comment/edit.html.twig', [
            'comment_form' => $form->createView()
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete', methods: ["POST"])]
    public function delete(Comment $comment, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($this->getUser() !== $comment->getPoster() && !$authorizationChecker->isGranted("ROLE_ADMIN")) {
            return $this->redirectToRoute('homepage');
        }

        $slug = $comment->getPost()->getSlug();
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('post_show', ['slug' => $slug]);
    }

    #[Route('/comment/review/{id}', name: 'review_comment')]
    #[IsGranted("ROLE_ADMIN")]
    public function reviewComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $approved = !$request->query->get('reject');

        $comment->setApproved($approved);
        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Comment reviewed, thank you!');

        return $this->redirectToRoute("post_show", ['slug' => $comment->getPost()->getSlug()]);
    }
}
