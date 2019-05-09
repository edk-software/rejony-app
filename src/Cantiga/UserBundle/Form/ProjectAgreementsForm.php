<?php

namespace Cantiga\UserBundle\Form;

use Cantiga\UserBundle\Entity\AgreementSignature;
use Cantiga\UserBundle\Validator\Constraint\ContainsPesel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectAgreementsForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'users',
        ]);
        $resolver->setDefined([ 'confirmation', 'lastSigned', 'signatures' ]);
        $resolver->setRequired([ 'confirmation', 'lastSigned', 'signatures' ]);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var string|null $confirmation */
        $confirmation = $options['confirmation'];
        /** @var AgreementSignature|null $lastSigned */
        $lastSigned = $options['lastSigned'];
        /** @var AgreementSignature[] $signatures */
        $signatures = $options['signatures'];

        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'maxlength' => 64,
                ],
                'data' => isset($lastSigned) ? $lastSigned->getFirstName() : null,
                'label' => 'FirstNameLabel',
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'maxlength' => 64,
                ],
                'data' => isset($lastSigned) ? $lastSigned->getLastName() : null,
                'label' => 'LastNameLabel',
                'required' => true,
            ])
            ->add('town', TextType::class, [
                'attr' => [
                    'maxlength' => 64,
                ],
                'data' => isset($lastSigned) ? $lastSigned->getTown() : null,
                'label' => 'TownLabel',
                'required' => true,
            ])
            ->add('zipCode', TextType::class, [
                'attr' => [
                    'maxlength' => 6,
                    'pattern' => '^[0-9]{2}-[0-9]{3}$',
                ],
                'data' => isset($lastSigned) ? $lastSigned->getZipCode() : null,
                'label' => 'ZipCodeLabel',
                'required' => true,
            ])
            ->add('street', TextType::class, [
                'attr' => [
                    'maxlength' => 64,
                ],
                'data' => isset($lastSigned) ? $lastSigned->getStreet() : null,
                'label' => 'StreetLabel',
                'required' => true,
            ])
            ->add('houseNo', TextType::class, [
                'attr' => [
                    'maxlength' => 16,
                ],
                'data' => isset($lastSigned) ? $lastSigned->getHouseNo() : null,
                'label' => 'HouseNoLabel',
                'required' => true,
            ])
            ->add('flatNo', TextType::class, [
                'attr' => [
                    'maxlength' => 16,
                ],
                'data' => isset($lastSigned) ? $lastSigned->getFlatNo() : null,
                'label' => 'FlatNoLabel',
                'required' => false,
            ])
            ->add('pesel', TextType::class, [
                'attr' => [
                    'maxlength' => 11,
                    'pattern' => '^[0-9]{11}$',
                ],
                'constraints' => [
                    new ContainsPesel(),
                ],
                'data' => isset($lastSigned) ? $lastSigned->getPesel() : null,
                'label' => 'PeselLabel',
                'required' => true,
            ])
            ->add('confirmation', CheckboxType::class, [
                'label' => $confirmation,
                'required' => true,
            ])
            ->add('accept', SubmitType::class, [
                'label' => 'Accept',
            ])
        ;
        foreach ($signatures as $signature) {
            $builder->add('signature_' . $signature->getId(), CheckboxType::class, [
                'label' => $signature->getAgreement()->getSummary(),
                'required' => true,
            ]);
        }
    }

    public function getName()
    {
        return 'ProjectAgreements';
    }
}
