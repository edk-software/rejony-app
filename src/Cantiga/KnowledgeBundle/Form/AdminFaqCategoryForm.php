<?php

namespace Cantiga\KnowledgeBundle\Form;

use Cantiga\KnowledgeBundle\Entity\FaqCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminFaqCategoryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'admin.faq.name',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'admin.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FaqCategory::class,
        ]);
    }

    public function getName()
    {
        return 'cantiga_knowledge_admin_faq_category';
    }
}