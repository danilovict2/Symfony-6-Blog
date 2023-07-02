<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;

class UnlinkPostTagsSubscriber implements EventSubscriberInterface
{
    public function onFormPreSetData(FormEvent $event): void
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

    public static function getSubscribedEvents(): array
    {
        return [
            'form.pre_set_data' => 'onFormPreSetData',
        ];
    }
}
