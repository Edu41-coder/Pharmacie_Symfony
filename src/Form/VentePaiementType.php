<?php

namespace App\Form;

use App\Entity\VentePaiement;
use App\Entity\Cheque;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class VentePaiementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modePaiement', ChoiceType::class, [
                'choices' => [
                    'Espèces' => 'especes',
                    'Carte Bancaire' => 'carte_bleu',
                    'Chèque' => 'cheque'
                ],
                'placeholder' => 'Mode de paiement',
                'attr' => [
                    'class' => 'form-select mode-paiement'
                ],
                'label' => 'Mode de paiement'
            ])
            ->add('montant', MoneyType::class, [
                'currency' => 'EUR',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control montant-paiement'
                ],
                'label' => 'Montant'
            ])
            ->add('numeroCheque', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control numero-cheque',
                    'placeholder' => 'N° de chèque'
                ],
                'label' => 'Numéro de chèque'
            ])
            ->add('datePaiement', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Date du paiement',
                'data' => new \DateTime()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VentePaiement::class,
        ]);
    }
}
