<?php

namespace App\Form;

use App\Entity\ACommander;
use App\Entity\Inventaire;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ACommanderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['edit_mode']) {
            $builder->add('produit', EntityType::class, [
                'class' => Inventaire::class,
                'choice_label' => function (Inventaire $inventaire) {
                    return $inventaire->getProduit()->getNom();
                },
                'label' => 'Produit',
                'placeholder' => 'Choisir un produit',
                'required' => true,
                'mapped' => false
            ]);
        }

        $builder->add('quantite', IntegerType::class, [
            'label' => 'Quantité',
            'required' => true,
            'attr' => [
                'min' => 1
            ]
        ]);

        if (!$options['edit_mode']) {
            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $aCommander = $event->getData();
                
                $inventaire = $form->get('produit')->getData();
                if ($inventaire instanceof Inventaire) {
                    $aCommander->setProduit($inventaire->getProduit());
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ACommander::class,
            'edit_mode' => false
        ]);
    }
} 