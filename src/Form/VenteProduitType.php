<?php

namespace App\Form;

use App\Entity\VenteProduit;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityRepository;

class VenteProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => function (Produit $produit) {
                    return $produit->getNom() . ' - ' . $produit->getPrixVenteHt() . '€';
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.isDeleted = :isDeleted')
                        ->setParameter('isDeleted', false)
                        ->orderBy('p.nom', 'ASC');
                },
                'placeholder' => 'Sélectionner un produit',
                'required' => true,
                'attr' => [
                    'class' => 'form-select produit-select'
                ],
                'label' => 'Produit'
            ])
            ->add('quantite', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control quantite-input',
                    'min' => 1
                ],
                'label' => 'Quantité',
                'data' => 1
            ])
            ->add('prix', HiddenType::class, [
                'mapped' => false,
                'attr' => ['class' => 'prix-produit']
            ])
            ->add('prescription', HiddenType::class, [
                'mapped' => false,
                'attr' => ['class' => 'prescription-produit']
            ])
            ->add('tauxRemboursement', HiddenType::class, [
                'mapped' => false,
                'attr' => ['class' => 'taux-remboursement-produit']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VenteProduit::class,
        ]);
    }
}
