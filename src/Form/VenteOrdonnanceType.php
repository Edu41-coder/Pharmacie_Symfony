<?php

namespace App\Form;

use App\Entity\VenteOrdonnance;
use App\Entity\Ordonnance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class VenteOrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ordonnance', EntityType::class, [
                'class' => Ordonnance::class,
                'choice_label' => function (Ordonnance $ordonnance) {
                    // Utilisation de numeroOrdonnance au lieu de dateOrdonnance
                    return 'Ordonnance #' . $ordonnance->getId() . ' - ' . $ordonnance->getNumeroOrdonnance();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.id', 'DESC'); // Tri par ID au lieu de dateOrdonnance
                },
                'placeholder' => 'Sélectionner une ordonnance',
                'required' => true,
                'attr' => [
                    'class' => 'form-select ordonnance-select'
                ],
                'label' => 'Ordonnance'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VenteOrdonnance::class,
        ]);
    }
}