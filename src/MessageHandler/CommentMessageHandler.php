<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Services\SpamChecker;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

#[AsMessageHandler]
final class CommentMessageHandler
{
    public function __construct(
        private CommentRepository $commentRepository,
        private SpamChecker $spamChecker,
        private MailerInterface $mailer,
        #[Autowire('%admin_email%')] private string $adminEmail,
    ) {
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }

        $score = $this->spamChecker->getSpamScore($comment, $message->getContext());

        if ($score === 0) {
            // Approve comment that is not a spam
            $comment->setApproved(true);
            $this->commentRepository->save($comment, true);
        } elseif ($score === 1) {
            // Send email to admin so he can approve or reject comment that is 'maybe spam'
            $email = (new TemplatedEmail())
                ->from($this->adminEmail)
                ->to($this->adminEmail)
                ->subject('Might Be Spam Comment')
                ->htmlTemplate('email/validate_comment.html.twig')
                ->context([
                    'comment' => $comment
                ])
            ;
            $this->mailer->send($email);
        }
    }
}
