<?php

namespace WIO\EdkBundle\Form;

use Cantiga\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerificationForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
        $resolver->setDefaults([
            'responsibleUsers' => [],
            'translation_domain' => 'messages',
        ]);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $builder
            ->add('responsibleId', ChoiceType::class, [
                'choices' => $this->getUsers($options['responsibleUsers']),
                'label' => 'Responsible user',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Start verification',
            ])
        ;
	}

	private function getUsers(array $users)
    {
        $list = [];
        /** @var User $user */
        foreach ($users as $user) {
            $list[$user->getName()] = $user->getId();
        }

        return $list;
    }

	public function getName()
	{
		return 'verification';
	}
}
