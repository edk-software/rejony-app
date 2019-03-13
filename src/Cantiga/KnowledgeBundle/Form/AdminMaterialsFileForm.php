<?php

namespace Cantiga\KnowledgeBundle\Form;

use Cantiga\KnowledgeBundle\Entity\MaterialsCategory;
use Cantiga\KnowledgeBundle\Entity\MaterialsFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class AdminMaterialsFileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', ChoiceType::class, [
                'choice_label' => function (MaterialsCategory $category) {
                    return $category->getName();
                },
                'choices' => $options['categories'],
                'label' => 'admin.materials.category',
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'maxlength' => 255,
                ],
                'label' => 'admin.materials.name',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'admin.materials.description',
            ])
        ;
        if ($options['isNew']) {
            $builder
                ->add('path', FileType::class, [
                    'label' => 'admin.materials.file',
                    'required' => false,
                ])
                ->add('url', UrlType::class, [
                    'attr' => [
                        'maxlength' => 255,
                    ],
                    'constraints' => [
                        new Constraints\Length([
                            'groups' => ['add'],
                            'max' => 255,
                        ]),
                        new Constraints\Regex([
                            'groups' => ['add'],
                            'message' => 'URL address needs to be available via HTTP protocol.',
                            'pattern' => '#^https?://#i',
                        ]),
                    ],
                    'label' => 'admin.materials.url',
                    'mapped' => false,
                    'required' => false,
                ])
            ;
        }
        $builder
            ->add('level', ChoiceType::class, [
                'choices' => array_flip(self::getLevels()),
                'label' => 'admin.level',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'admin.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'categories' => [],
            'data_class' => MaterialsFile::class,
            'isNew' => true,
            'validation_groups' => [
                'add',
                'edit',
            ],
        ]);
    }

    public function getName()
    {
        return 'cantiga_knowledge_admin_materials_file';
    }

    public static function getLevels()
    {
        $levels = [
            MaterialsFile::LEVEL_ALL => 'admin.level.all',
            MaterialsFile::LEVEL_AREA => 'admin.level.area',
            MaterialsFile::LEVEL_GROUP => 'admin.level.group',
            MaterialsFile::LEVEL_PROJECT => 'admin.level.project',
        ];

        return $levels;
    }
}
