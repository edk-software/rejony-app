<?php

namespace WIO\EdkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreaRoutesImportForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
        $resolver->setDefaults([
            'areasRoutes' => [],
            'translation_domain' => 'messages',
        ]);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $builder
            ->add('areaId', ChoiceType::class, [
                'choices' => $this->getAreas($options['areasRoutes']),
                'label' => 'Select area',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Import',
            ])
        ;
	}

	private function getAreas(array $areas)
    {
        $list = [];
        foreach ($areas as $area) {
            $routeText = $area['routes'];
            if (strlen($area['routes'])>100)
            {
                $routeText = substr($area['routes'],0,100).'...';
            }
            $list[$area['area'].' ('.$routeText.')'] = $area['id'];
        }

        return $list;
    }

	public function getName()
	{
		return 'import';
	}
}
