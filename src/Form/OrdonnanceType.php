<?php

namespace App\Form;

use App\Entity\Ordonnance;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\File;

class OrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroOrdonnance', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro d\'ordonnance'
                ],
                'label' => 'Numéro d\'ordonnance'
            ])
            ->add('numeroDOrdre', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro d\'ordre'
                ],
                'label' => 'Numéro d\'ordre'
            ])
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG) ou un PDF'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Image de l\'ordonnance'
            ])
            ->add('produits', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.prescription = :prescription')
                        ->andWhere('p.isDeleted = :isDeleted')
                        ->setParameter('prescription', 'oui')
                        ->setParameter('isDeleted', false)
                        ->orderBy('p.nom', 'ASC');
                },
                'attr' => [
                    'class' => 'form-select'
                ],
                'label' => 'Produits prescrits'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ordonnance::class,
        ]);
    }
}
