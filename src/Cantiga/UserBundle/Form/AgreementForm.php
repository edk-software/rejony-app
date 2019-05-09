<?php

namespace Cantiga\UserBundle\Form;

use Cantiga\UserBundle\Entity\Agreement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgreementForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agreement::class,
            'translation_domain' => 'users',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'maxlength' => 255,
                ],
                'label' => 'TitleLabel',
                'required' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'ContentLabel',
                'required' => false,
            ])
            ->add('url', UrlType::class, [
                'attr' => [
                    'maxlength' => 255,
                ],
                'label' => 'UrlLabel',
                'required' => false,
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'SummaryLabel',
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
        ;
    }

    public function getName()
    {
        return 'Agreement';
    }
}
