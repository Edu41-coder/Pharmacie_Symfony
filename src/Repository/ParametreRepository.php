<?php

namespace App\Repository;

use App\Entity\Parametre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parametre>
 */
class ParametreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametre::class);
    }

    /**
     * Récupère un paramètre par son nom.
     */
    public function findByName(string $nom): ?Parametre
    {
        return $this->find($nom);
    }
    
    /**
     * Récupère la valeur d'un paramètre par son nom.
     */
    public function getParametreValue(string $nom): ?string
    {
        $parametre = $this->find($nom);
        return $parametre ? $parametre->getValeur() : null;
    }
    
    /**
     * Met à jour ou crée un paramètre.
     */
    public function saveParametre(string $nom, string $valeur): void
    {
        $entityManager = $this->getEntityManager();
        $parametre = $this->find($nom);
        
        if (!$parametre) {
            $parametre = new Parametre();
            $parametre->setNom($nom);
        }
        
        $parametre->setValeur($valeur);
        $entityManager->persist($parametre);
        $entityManager->flush();
    }
    
    /**
     * Récupère la valeur de la TVA.
     */
    public function getTVA(): float
    {
        $tva = $this->getParametreValue(Parametre::TVA_KEY());
        return $tva !== null ? (float) $tva : 5.0; // Valeur par défaut de 5%
    }
}
