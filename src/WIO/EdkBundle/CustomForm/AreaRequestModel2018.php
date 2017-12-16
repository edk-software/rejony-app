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
declare(strict_types=1);
namespace WIO\EdkBundle\CustomForm;

use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\CustomFormRendererInterface;
use Cantiga\Metamodel\CustomForm\CustomFormSummaryInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Cantiga\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AreaRequestModel2018 implements CustomFormModelInterface
{
	private $translator;
	
	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}
	
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('routeFrom', TextType::class, array('label' => 'Beginning of the route',  'attr' => array('placeholder' => 'Beginning of the route help'), 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 50])
		]));
		$builder->add('routeTo', TextType::class, array('label' => 'End of the route',  'attr' => array('placeholder' => 'End of the route help'), 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 50])
		]));
		$builder->add('routeLength', NumberType::class, array('label' => 'Route length (km)', 'constraints' => [
			new Range(['min' => 20])
		]));
		$builder->add('routeAscent', NumberType::class, array('label' => 'Route ascent (m)', 'constraints' => [
			new Range(['min' => 1, 'max' => 5000])
		]));

		$builder->add('isParticipant', BooleanType::class, ['label' => 'IsParticipantFormLabel', 'required' => true, 'disabled' => false]);
		$builder->add('ewcMeaning', TextareaType::class, array('label' => 'EWCMeaningFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
			new NotNull,
			new Length(['min' => 10, 'max' => 400])
		]));
		$builder->add('isAreaCreated', BooleanType::class, ['label' => 'IsAreaCreatedFormLabel', 'required' => true, 'disabled' => false]);
		$builder->add('whyCreatingArea', TextareaType::class, array('label' => 'WhyCreatingAreaFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
			new NotNull,
			new Length(['min' => 10, 'max' => 400])
		]));
		$builder->add('projectMgmtExperiences', TextareaType::class, array('label' => 'ProjectMgmtFormLabel', 'attr' => ['help_text' => 'Max400Chars'], 'constraints' => [
            new NotNull,
            new Length(['min' => 10, 'max' => 400])
        ]));
		$builder->add('participantCount', IntegerType::class, array('label' => 'ParticipantCountFormLabel', 'attr' => ['help_text' => 'Max10000'], 'constraints' => [
            new Range(['min' => 1, 'max' => 10000])
        ]));
		$builder->add('stationaryCourse', ChoiceType::class, ['label' => 'StationaryCoursePreferenceLabel', 'choices' => array_flip($this->stationaryCourseTypes()), 'multiple' => true, 'expanded' => true, 'constraints' => new Count(
				['min' => 1, 'minMessage' => 'Please select at least one option']
			)]);
	}
	
	public function validateForm(array $data, ExecutionContextInterface $context)
	{

		/*if ($data['ewcType'] == 'full') {
			if ($data['routeLength'] < 30) {
				$violation = $context->buildViolation('For full areas, the route length cannot be lower than 30 kilometers.')
					->atPath('[routeLength]')
					->addViolation();
				return false;
			} else if($data['routeLength'] >= 30 && $data['routeLength'] < 40 && $data['routeAscent'] < 500) {
				$context->buildViolation('The route ascent must be greater than 500 meters, if the length is between 30 and 40 kilometers.')
					->atPath('[routeAscent]')
					->addViolation();
				return false;
			}
		}*/
		return true;
	}
	
	public function createFormRenderer(): CustomFormRendererInterface
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('Proposed route', 'ProposedRouteInformationText');
		$r->fields('routeFrom', 'routeTo');
		$r->fields('routeLength', 'routeAscent');

		$r->group('EWC practise');
		$r->fields('isParticipant', 'ewcMeaning', 'isAreaCreated', 'whyCreatingArea', 'projectMgmtExperiences','participantCount');
		$r->group('Stationary course');
		$r->fields('stationaryCourse');
		return $r;
	}
	
	public function createSummary(): CustomFormSummaryInterface
	{
		$s = new DefaultCustomFormSummary();

		$s->present('routeFrom', 'Beginning of the route', 'string');
		$s->present('routeTo', 'End of the route', 'string');
		$s->present('routeLength', 'Route length', 'callback', function($length) {
			return $length.' km';
		});
		$s->present('routeAscent', 'Route ascent', 'callback', function($length) {
			return $length.' m';
		});
		$s->present('isParticipant', 'IsParticipantFormLabel', 'boolean');
		$s->present('ewcMeaning', 'EWCMeaningFormLabel', 'string');

        $s->present('isAreaCreated', 'IsAreaCreatedFormLabel', 'boolean');
        $s->present('whyCreatingArea', 'WhyCreatingAreaFormLabel', 'string');

		$s->present('projectMgmtExperiences', 'ProjectMgmtFormLabel', 'string');
		$s->present('participantCount', 'ParticipantCountFormLabel', 'string');

		$s->present('stationaryCourse', 'StationaryCoursePreferenceLabel', 'callback', function($options) {
			if (!is_array($options)) {
				return '---';
			}
			$code = '<ul>';
			$mapping = $this->stationaryCourseTypes();
			foreach ($options as $option) {
				$code .= '<li>'.$this->translator->trans($mapping[$option]).'</li>';
			}
			$code .= '</ul>';
			return $code;			
		});
		return $s;
	}
	
	public function ewcTypes($routeLength, $routeAscent)
	{
        if ($routeLength >= 30) {
            return 'Extreme Way of the Cross';
        }

        elseif ($routeLength >= 30 && $routeLength < 40 && $routeAscent >= 500){

            return 'Extreme Way of the Cross';
        }
        else
            return 'Inspired by Extreme Way of the Cross';
	}
	
        public function stationaryCourseTypes()
	{
		return [
            1 => '20.01.2017 - Kraków',
            2 => '27.01.2017 - Poznań',
            3 => '10.02.2017 - Wrocław',
            4 => '10.02.2017 - Warszawa',
            5 => '17.02.2017 - Kraków',
            6 => 'szkolenie zdalne - rejon zagraniczny',
            7 => 'nie będe uczestniczył - tworzyłem rejon w zeszłym roku',
        ];
	}
}
