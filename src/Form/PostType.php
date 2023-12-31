<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Tag;
use App\EventSubscriber\PostTagsSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'error_bubbling' => true,
            ])
            ->add('body', TextareaType::class, [
                'error_bubbling' => true,
                'purify_html' => true
            ])
            ->add('slug', null, [
                'error_bubbling' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'required' => false,
            ])
            ->add('submit', SubmitType::class)
        ;
        $builder->addEventSubscriber(new PostTagsSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
