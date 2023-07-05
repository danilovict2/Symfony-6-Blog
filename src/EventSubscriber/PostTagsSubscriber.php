<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PostTagsSubscriber implements EventSubscriberInterface
{
    public function preSetData(FormEvent $event): void
    {
        $post = $event->getData();
        if (!$post) {
            return;
        }

        $tags = $post->getTags();
        foreach ($tags as $tag) {
            $tag->removePost($post);
        }
    }

    public function postSubmit(FormEvent $event): void
    {
        $post = $event->getData();
        $tags = $post->getTags();

        foreach ($tags as $tag) {
            $tag->addPost($post);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT => 'postSubmit'
        ];
    }
}
