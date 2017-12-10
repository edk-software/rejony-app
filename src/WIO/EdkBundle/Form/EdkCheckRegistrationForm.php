<?php

namespace WIO\EdkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EdkCheckRegistrationForm extends AbstractType
{
	const TYPE_CHECK = 0;
	const TYPE_REMOVE = 1;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'public'
        ));
    }
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
            ->add('k', TextType::class, [
                'label' => 'Your access code',
                'required' => true,
            ])
            ->add('t', ChoiceType::class, [
                'choices' => [
                    'I want to check my status' => self::TYPE_CHECK,
                    'I want to remove my request' => self::TYPE_REMOVE,
                ],
                'expanded' => true,
                'label' => false,
                'required' => true,
            ])
            ->add('execute', SubmitType::class, [
                'label' => 'Execute',
            ])
        ;
	}
	
	public function getName()
    {
		return 'edk_check_registration';
	}
}
