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
use Doctrine\ORM\EntityRepository;

class ACommanderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $excludeProduits = $options['exclude_produits'] ?? [];

        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner un produit',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($excludeProduits) {
                    $qb = $er->createQueryBuilder('p')
                        ->where('p.isDeleted = :isDeleted')
                        ->setParameter('isDeleted', false)
                        ->orderBy('p.nom', 'ASC');
                    
                    if (!empty($excludeProduits)) {
                        $qb->andWhere('p.id NOT IN (:ids)')
                           ->setParameter('ids', $excludeProduits);
                    }
                    
                    return $qb;
                },
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
            ]);

        if ($options['edit_mode']) {
            // En mode édition, on désactive la sélection du produit
            $builder->get('produit')->setDisabled(true);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneACommander::class,
            'edit_mode' => false,
            'exclude_produits' => []
        ]);
    }
}