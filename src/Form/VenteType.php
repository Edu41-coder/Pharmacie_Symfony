<?php

namespace App\Form;

use App\Entity\Vente;
use App\Entity\Client;
use App\Entity\Ordonnance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityManagerInterface;

class VenteType extends AbstractType
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function (Client $client) {
                    return $client->getNom() . ' ' . $client->getPrenom();
                },
                'placeholder' => 'Sélectionner un client',
                'required' => false,
                'attr' => [
                    'class' => 'form-select client-select'
                ],
                'label' => 'Client'
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Date de vente'
            ])
            ->add('montant', MoneyType::class, [
                'currency' => 'EUR',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
                'label' => 'Montant total',
                'mapped' => true
            ])
            ->add('montantRegle', MoneyType::class, [
                'currency' => 'EUR',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
                'label' => 'Montant réglé',
                'mapped' => true
            ])
            ->add('aRembourser', MoneyType::class, [
                'currency' => 'EUR',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
                'label' => 'Montant à rembourser',
                'mapped' => true
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'label' => 'Commentaire'
            ])
            ->add('venteProduits', CollectionType::class, [
                'entry_type' => VenteProduitType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false
            ])
            ->add('ventePaiements', CollectionType::class, [
                'entry_type' => VentePaiementType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false
            ])
            // Nous avons simplement commenté ce champ pour éviter les problèmes
            // ->add('ordonnances', EntityType::class, [
            //     'class' => Ordonnance::class,
            //     'choice_label' => function (Ordonnance $ordonnance) {
            //         return $ordonnance->getNumeroOrdonnance() . ' - ' . $ordonnance->getNumeroDOrdre();
            //     },
            //     'multiple' => true,
            //     'expanded' => false,
            //     'required' => false,
            //     'attr' => [
            //         'class' => 'form-select ordonnance-select'
            //     ],
            //     'label' => 'Ordonnances'
            // ])
            ->add('isDeleted', HiddenType::class, [
                'data' => '0'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vente::class,
        ]);
    }
}
