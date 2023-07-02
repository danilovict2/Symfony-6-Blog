<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
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
    public function delete(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $slug = $comment->getPost()->getSlug();
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('post_show', ['slug' => $slug]);
    }
}