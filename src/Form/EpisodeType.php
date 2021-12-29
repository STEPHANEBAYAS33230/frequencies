<?php

namespace App\Form;

use App\Entity\Episode;
use App\Entity\Serie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EpisodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero')
            ->add('brochure', FileType::class, [
                'label' => 'fichier audio (mp3, ogg, wav)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'audio/x-mp3',
                            'audio/ogg',
                            'audio/x-wav',
                            'audio/mpeg',
                        ],
                        'mimeTypesMessage' => 'svp télécharger un fichier audio valable.',
                    ])
                ],
            ])
            ->add('nom_episode')
            ->add('serie', EntityType::class,['class' =>Serie::class,'choice_value'=>'nom', 'attr' => [ 'readonly'=>'true', 'required'=>'true']])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Episode::class,
        ]);
    }
}
