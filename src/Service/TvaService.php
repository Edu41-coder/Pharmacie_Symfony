<?php

namespace App\Service;

use App\Entity\Tva;
use Doctrine\ORM\EntityManagerInterface;

class TvaService
{
    private const DEFAULT_TVA_RATE = 20.0;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Récupère l'entité TVA actuelle ou en crée une si elle n'existe pas
     *
     * @return Tva
     */
    public function getCurrentTva(): Tva
    {
        $tvaRepository = $this->entityManager->getRepository(Tva::class);
        $tva = $tvaRepository->find('taux_tva'); // Cherche l'entrée avec nom = 'taux_tva'

        if (!$tva) {
            // Crée une nouvelle entrée si elle n'existe pas
            $tva = new Tva();
            $tva->setTaux(self::DEFAULT_TVA_RATE); // Valeur par défaut
            
            $this->entityManager->persist($tva);
            $this->entityManager->flush();
        }

        return $tva;
    }

    /**
     * Calcule le montant TTC à partir d'un montant HT
     *
     * @param float $montantHT
     * @return float
     */
    public function calculateTTC(float $montantHT): float
    {
        $tva = $this->getCurrentTva();
        return $montantHT * (1 + ($tva->getTaux() / 100));
    }

    /**
     * Calcule le montant de la TVA à partir d'un montant HT
     *
     * @param float $montantHT
     * @return float
     */
    public function calculateTVAAmount(float $montantHT): float
    {
        $tva = $this->getCurrentTva();
        return $montantHT * ($tva->getTaux() / 100);
    }
}
