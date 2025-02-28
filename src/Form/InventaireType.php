<?php

namespace App\Form;

use App\Entity\Inventaire;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Si c'est un formulaire d'édition, on n'affiche que le stock
        if ($options['is_edit']) {
            $builder->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'required' => true,
                'attr' => [
                    'min' => 0
                ]
            ]);
        } else {
            // Pour la création, on garde tous les champs
            $builder
                ->add('produit', EntityType::class, [
                    'class' => Produit::class,
                    'choice_label' => 'nom',
                    'label' => 'Produit',
                    'required' => true,
                    'choices' => $options['produits_existants'],
                    'placeholder' => 'Sélectionnez un produit',
                ])
                ->add('stock', IntegerType::class, [
                    'label' => 'Stock',
                    'required' => true,
                    'attr' => [
                        'min' => 0
                    ]
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inventaire::class,
            'produits_existants' => [],
            'is_edit' => false,
        ]);
    }
} 