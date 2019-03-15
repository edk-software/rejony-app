<?php

namespace Cantiga\UserBundle\Form;

use Cantiga\UserBundle\Entity\AgreementSignature;
use Cantiga\UserBundle\Validator\Constraint\ContainsPesel;
use DateTime;
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
        $resolver->setDefined([ 'lastSigned', 'signature' ]);
        $resolver->setRequired([ 'lastSigned', 'signature' ]);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AgreementSignature|null $lastSigned */
        $lastSigned = $options['lastSigned'];
        /** @var AgreementSignature $signature */
        $signature = $options['signature'];

        $year = (int) date('Y');
        $defaultDate = new DateTime(($year - 18) . '-01-01');

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
            ->add('signature', CheckboxType::class, [
                'label' => $signature->getAgreement()->getSummary(),
                'required' => true,
            ])
            ->add('accept', SubmitType::class, [
                'label' => 'Accept',
            ])
        ;
    }

    public function getName()
    {
        return 'ProjectAgreements';
    }
}
