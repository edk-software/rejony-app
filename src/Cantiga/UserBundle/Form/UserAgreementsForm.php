<?php

namespace Cantiga\UserBundle\Form;

use Cantiga\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAgreementsForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined([
		    'termsOfUseLabel',
		    'marketingAgreementLabel',
		    'personalDataLabel',
            'user',
        ]);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    /** @var User $user */
	    $user = $options['user'];
		$builder
			->add('acceptTermsOfUse', CheckboxType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'data' => $user->isTermsOfUseAccepted(),
			    'label' => $options['termsOfUseLabel'],
                'required' => true,
            ])
			->add('allowPersonalData', CheckboxType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'data' => $user->isPersonalDataAllowed(),
			    'label' => $options['personalDataLabel'],
                'required' => true,
            ])
			->add('marketingAgreement', CheckboxType::class, [
			    'data' => $user->hasMarketingAgreement(),
			    'label' => $options['marketingAgreementLabel'],
                'required' => false,
            ])
			->add('save', SubmitType::class, [
			    'label' => 'Save',
            ])
        ;
	}

	public function getName()
	{
		return 'UserAgreements';
	}
}
