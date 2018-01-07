<?php

namespace Cantiga\KnowledgeBundle\Form;

use Cantiga\KnowledgeBundle\Entity\FaqCategory;
use Cantiga\KnowledgeBundle\Entity\FaqQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminFaqQuestionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', ChoiceType::class, [
                'choice_label' => function (FaqCategory $category) {
                    return $category->getName();
                },
                'choices' => $options['categories'],
                'label' => 'admin.faq.category',
            ])
            ->add('topic', TextType::class, [
                'label' => 'admin.faq.topic',
            ])
            ->add('answer', TextareaType::class, [
                'label' => 'admin.faq.answer',
            ])
            ->add('level', ChoiceType::class, [
                'choices' => array_flip(self::getLevels()),
                'label' => 'admin.level',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'admin.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'categories' => [],
            'data_class' => FaqQuestion::class,
        ]);
    }

    public function getName()
    {
        return 'cantiga_knowledge_admin_faq_question';
    }

    public static function getLevels()
    {
        $levels = [
            FaqQuestion::LEVEL_ALL => 'admin.level.all',
            FaqQuestion::LEVEL_AREA => 'admin.level.area',
            FaqQuestion::LEVEL_GROUP => 'admin.level.group',
            FaqQuestion::LEVEL_PROJECT => 'admin.level.project',
        ];

        return $levels;
    }
}