<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace WIO\EdkBundle\Form;

use Cantiga\CoreBundle\Repository\AreaRepositoryInterface;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WIO\EdkBundle\Entity\EdkRoute;

class EdkRouteForm extends AbstractType
{
	const ADD = 0;
	const EDIT = 1;

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['mode', 'areaRepository', 'isPlaceManager']);
		$resolver->setRequired(['mode']);
		
		$resolver->setDefaults(array(
			'translation_domain' => 'edk'
		));
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    $isPlaceManager = (bool) $options['isPlaceManager'];
		if (!empty($options['areaRepository'])) {
			$builder->add('area', ChoiceType::class, ['label' => 'Area', 'choices' => $options['areaRepository']->getFormChoices()]);
			$builder->get('area')->addModelTransformer(new EntityTransformer($options['areaRepository']));
		}

		if ($isPlaceManager) {
			$builder->add('routeType', ChoiceType::class, [
				'choices' => [
					'UndefinedRoute' => EdkRoute::TYPE_UNDEFINED,
					'FullRoute' => EdkRoute::TYPE_FULL,
					'RouteInspiredByEWC' => EdkRoute::TYPE_INSPIRED,
				],
				'label' => 'Route type',
			]);
		}
		$builder
			->add('name', TextType::class, array('label' => 'Route name','attr' => array('help_text' => 'Name helptext','placeholder' => 'Name placeholder')))
            ->add('routePatron', TextType::class, array('label' => 'Route patron','required' => false,'attr' => array('placeholder' => 'Patron placeholder')))
            ->add('routeColor', TextType::class, array('label' => 'Route color','required' => false,'attr' => array('placeholder' => 'Color placeholder')))
            ->add('routeAuthor', TextType::class, array('label' => 'Route author','required' => false, 'attr' => array('help_text' => 'Author helptext', 'placeholder' => 'Author placeholder')))
			->add('routeCourse', TextareaType::class, array('label' => 'Route course', 'attr' => array('help_text' => 'RouteCourseInfoText', 'placeholder'=>'RouteCoursePlaceholder')))
			->add('routeFrom', TextType::class, 
				array('label' => 'Route beginning', 'attr' => array('help_text' => '(settlement)','placeholder' => 'From placeholder'))
			)
            ->add('routeFromDetails', TextType::class,
                array('label' => 'Route beginning details', 'required' => false, 'attr' => array('help_text' => '(settlement details)', 'placeholder' => 'From Details placeholder'))
            )
			->add('routeTo', TextType::class,
                array('label' => 'Route end', 'attr' => array('help_text' => '(settlement)','placeholder' => 'From placeholder'))
            )
            ->add('routeToDetails', TextType::class,
                array('label' => 'Route end details', 'required' => false, 'attr' => array('help_text' => '(settlement details)','placeholder' => 'From Details placeholder'))
            )
        ;
		if ($isPlaceManager) {
			$builder
				->add('routeLength', IntegerType::class, [
					'label' => 'Route length (km)',
				])
				->add('routeAscent', IntegerType::class, [
					'attr' => ['help_text' => 'RouteAscentInfoText'],
					'label' => 'Route ascent (m)',
				])
			;
		}
		$builder
			->add('routeObstacles', TextType::class, 
				array('label' => 'Additional obstacles', 'required' => false)
			)
			->add('descriptionFileUpload', FileType::class, array('label' => 'RouteDescriptionFileUpload', 'required' => false))
			->add('mapFileUpload', FileType::class, array('label' => 'RouteMapFileUpload', 'required' => false, 'attr' => array('help_text' => 'RouteMapCopyrightInformationText')))
			->add('gpsTrackFileUpload', FileType::class, array('label' => 'RouteGPSTraceFileUpload', 'required' => $options['mode'] == self::ADD))
			->add('save', SubmitType::class, array('label' => 'Save'))
		;
	}
	
	public function getName() {
		return 'EdkRoute';
	}
}
