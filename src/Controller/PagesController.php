<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(PostRepository $postRepository): Response
    {
        $recentPosts = $postRepository->findBy([], ['created_at' => 'DESC'], 4);
        return $this->render('pages/index.html.twig', [
            'posts' => $recentPosts
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

    #[Route('/contact/email/send', name: 'contact_email_send', methods: ['POST'])]
    public function sendContactEmail(Request $request, MailerInterface $mailer, #[Autowire('%admin_email%')] string $adminEmail): Response
    {
        $email = (new TemplatedEmail())
            ->from($request->request->get('email'))
            ->to($adminEmail)
            ->subject($request->request->get('subject'))
            ->htmlTemplate('email/contact.html.twig')
            ->context([
                'message' => $request->request->get('message'),
                'sender_email' => $request->request->get('email')
            ])
        ;
        $mailer->send($email);
        return $this->redirectToRoute('homepage');
    }
}
