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
namespace Cantiga\CoreBundle\CustomForm;

use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\CustomFormRendererInterface;
use Cantiga\Metamodel\CustomForm\CustomFormSummaryInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * The default model for area requests contains a couple of commonly needed properties
 * that should cover most of the basic use cases.
 */
class DefaultAreaRequestModel implements CustomFormModelInterface
{
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('orgName', TextType::class, ['label' => 'OrgNameLabel', 'required' => false, 'constraints' => [
			new Length(['min' => 2, 'max' => 40])
		]]);
		$builder->add('orgWebsite', TextType::class, ['label' => 'OrgWebsiteLabel', 'required' => false, 'constraints' => [
			new Url()
		]]);
		$builder->add('areaDescription', TextareaType::class, array('label' => 'AreaDescriptionLabel', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 1000])
		]));
		$builder->add('motivation', TextareaType::class, array('label' => 'MotivationLabel', 'constraints' => [
			new NotNull,
			new Length(['min' => 2, 'max' => 1000])
		]));
	}
	
	public function validateForm(array $data, ExecutionContextInterface $context)
	{
	}
	
	public function createFormRenderer(): CustomFormRendererInterface
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('Area information');
		$r->fields('areaDescription', 'motivation');
		$r->group('Your organization', 'YourOrganizationInfoText');
		$r->fields('orgName', 'orgWebsite');
		return $r;
	}
	
	public function createSummary(): CustomFormSummaryInterface
	{
		$s = new DefaultCustomFormSummary();
		$s->present('areaDescription', 'AreaDescriptionLabel', DefaultCustomFormSummary::TYPE_STRING);
		$s->present('motivation', 'MotivationLabel', DefaultCustomFormSummary::TYPE_STRING);
		$s->present('orgName', 'OrgNameLabel', DefaultCustomFormSummary::TYPE_STRING);
		$s->present('orgWebsite', 'OrgWebsiteLabel', DefaultCustomFormSummary::TYPE_URL);
		return $s;
	}
}
