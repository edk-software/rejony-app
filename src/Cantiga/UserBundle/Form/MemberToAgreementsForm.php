<?php

namespace Cantiga\UserBundle\Form;

use Cantiga\UserBundle\Entity\Agreement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberToAgreementsForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'general',
        ]);
        $resolver->setDefined([ 'agreements' ]);
        $resolver->setRequired([ 'agreements' ]);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Agreement[] $agreements */
        $agreements = $options['agreements'];

        foreach ($agreements as $agreement) {
            $signature = $agreement->getSignatures()->get(0);
            $builder->add('agreement_' . $agreement->getId(), ChoiceType::class, [
                'choices' => [
                    'No' => false,
                    'Yes' => true,
                ],
                'data' => isset($signature),
                'expanded' => true,
                'label' => false,
                'multiple' => false,
                'required' => true,
            ]);
        }
        $builder->add('save', SubmitType::class, [
            'label' => 'Save',
        ]);
    }

    public function getName()
    {
        return 'MemberToAgreements';
    }
}
