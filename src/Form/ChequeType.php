<?php

namespace App\Form;

use App\Entity\Cheque;
use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ChequeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroCheque', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro du chèque'
                ],
                'label' => 'Numéro du chèque'
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function (Client $client) {
                    return $client->getNom() . ' ' . $client->getPrenom();
                },
                'placeholder' => 'Client',
                'attr' => [
                    'class' => 'form-select'
                ],
                'label' => 'Client'
            ])
            ->add('montant', MoneyType::class, [
                'currency' => 'EUR',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Montant'
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'En attente' => Cheque::ETAT_EN_ATTENTE,
                    'Validé' => Cheque::ETAT_VALIDE,
                    'Refusé' => Cheque::ETAT_REFUSE
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'label' => 'État'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cheque::class,
        ]);
    }
}
