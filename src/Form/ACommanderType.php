<?php

namespace App\Form;

use App\Entity\LigneACommander;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class ACommanderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner un produit',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un produit'
                    ])
                ],
                'disabled' => $options['edit_mode'] ?? false,
                'attr' => [
                    'class' => 'select2'
                ],
                'label' => 'Produit'
            ])
            ->add('quantite', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'min' => 1
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une quantité'
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 1,
                        'message' => 'La quantité doit être supérieure ou égale à 1'
                    ])
                ],
                'label' => 'Quantité à commander'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneACommander::class,
            'edit_mode' => false,
        ]);
    }
}