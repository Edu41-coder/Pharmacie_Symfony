<?php

namespace App\Service;

use App\Entity\ACommander;
use App\Repository\ACommanderRepository;
use App\Repository\InventaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Inventaire;
use App\Entity\CreationCommander;
use Psr\Log\LoggerInterface;
use App\Entity\LigneACommander;

class ACommanderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ACommanderRepository $aCommanderRepository,
        private InventaireRepository $inventaireRepository,
        private LoggerInterface $logger
    ) {}

    public function createACommander(array $data): ACommander
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $aCommander = new ACommander();
            
            if (isset($data['produit'])) {
                $aCommander->setProduit($data['produit']);
            }
            
            if (isset($data['quantite'])) {
                $aCommander->setQuantite($data['quantite']);
            }

            $this->entityManager->persist($aCommander);
            $this->entityManager->flush();

            // Créer l'entrée dans creation_a_commander
            $creationCommander = new CreationCommander();
            $creationCommander->setACommander($aCommander);
            $creationCommander->setCreatedAt(new \DateTime());
            
            $this->entityManager->persist($creationCommander);
            $this->entityManager->flush();
            
            $connection->commit();

            return $aCommander;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    public function createSortedQuery(string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['p.nom', 'ac.quantite', 'ac.id', 'cc.createdAt'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'ac.id';
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

        return $this->aCommanderRepository->createQueryBuilder('ac')
            ->select('ac, p, cc')
            ->join('ac.produit', 'p')
            ->leftJoin('ac.creationCommander', 'cc')
            ->orderBy($sortField, $sortOrder);
    }

    public function createFromInventory(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        
        try {
            $inventaires = $this->inventaireRepository->findAll();
            if (empty($inventaires)) {
                throw new \Exception('Aucun inventaire trouvé');
            }

            // Créer une seule entrée dans a_commander
            $aCommander = new ACommander();
            $this->entityManager->persist($aCommander);
            $this->entityManager->flush();

            // Créer une seule entrée dans creation_commander
            $creationCommander = new CreationCommander();
            $creationCommander->setACommander($aCommander);
            $creationCommander->setCreatedAt(new \DateTime());
            $this->entityManager->persist($creationCommander);
            $this->entityManager->flush();
            
            foreach ($inventaires as $inventaire) {
                $produit = $inventaire->getProduit();
                if (!$produit) {
                    continue;
                }
                
                // Créer une ligne de produit pour la liste
                $ligneProduit = new LigneACommander();
                $ligneProduit->setACommander($aCommander);
                $ligneProduit->setProduit($produit);
                $ligneProduit->setQuantite(1);
                
                $this->entityManager->persist($ligneProduit);
            }
            
            $this->entityManager->flush();
            $connection->commit();
            
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error('Erreur lors de la création: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function createFromLowStock(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        
        try {
            $inventaires = $this->inventaireRepository->findLowStock();
            if (empty($inventaires)) {
                throw new \Exception('Aucun produit en stock insuffisant trouvé');
            }

            $createdAt = new \DateTime();
            
            $this->logger->info('Début de la création depuis les stocks insuffisants');
            $this->logger->debug('Nombre de produits en stock insuffisant trouvés: ' . count($inventaires));
            
            foreach ($inventaires as $inventaire) {
                $produit = $inventaire->getProduit();
                if (!$produit) {
                    continue;
                }
                
                // Calculer la quantité à commander basée sur le stock minimum
                $quantiteACommander = $inventaire->getStockMinimum() - $inventaire->getQuantite();
                if ($quantiteACommander <= 0) {
                    continue;
                }
                
                $aCommander = new ACommander();
                $aCommander->setProduit($produit);
                $aCommander->setQuantite($quantiteACommander);
                
                $this->entityManager->persist($aCommander);
                $this->entityManager->flush();
                
                $creationCommander = new CreationCommander();
                $creationCommander->setACommander($aCommander);
                $creationCommander->setCreatedAt($createdAt);
                
                $this->entityManager->persist($creationCommander);
                $this->entityManager->flush();
            }
            
            $connection->commit();
            
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error('Erreur lors de la création: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function updateACommander(ACommander $aCommander, array $data): void
    {
        $this->hydrateACommander($aCommander, $data);
        $this->entityManager->flush();
    }

    public function deleteACommander(ACommander $aCommander): void
    {
        $this->entityManager->remove($aCommander);
        $this->entityManager->flush();
    }

    private function hydrateACommander(ACommander $aCommander, array $data): void
    {
        if (isset($data['produit'])) {
            $aCommander->setProduit($data['produit']);
        }
        if (isset($data['quantite'])) {
            $aCommander->setQuantite($data['quantite']);
        }
    }

    public function getListesQuery(string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['cc.created_at', 'ac.id'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'cc.created_at';
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

        return $this->aCommanderRepository->createQueryBuilder('ac')
            ->select('ac, cc')
            ->join('ac.creationCommander', 'cc')
            ->orderBy($sortField, $sortOrder)
            ->groupBy('cc.created_at');
    }

    public function countProductsByDate(\DateTime $createdAt): int
    {
        return $this->aCommanderRepository->createQueryBuilder('ac')
            ->select('COUNT(ac.id)')
            ->join('ac.creationCommander', 'cc')
            ->where('cc.createdAt = :createdAt')
            ->setParameter('createdAt', $createdAt)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getListe(int $id): ?ACommander
    {
        return $this->aCommanderRepository->find($id);
    }

    public function getProduitsListe(int $listeId, string $sortField, string $sortOrder): array
    {
        $allowedFields = ['p.nom', 'ac.quantite'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'p.nom';
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

        return $this->aCommanderRepository->createQueryBuilder('ac')
            ->select('ac, p')
            ->join('ac.produit', 'p')
            ->join('ac.creationCommander', 'cc')
            ->where('ac.id = :listeId')
            ->setParameter('listeId', $listeId)
            ->orderBy($sortField, $sortOrder)
            ->getQuery()
            ->getResult();
    }

    public function getProduitFromListe(int $liste_id, int $produit_id): ?ACommander
    {
        return $this->aCommanderRepository->createQueryBuilder('ac')
            ->select('ac, p')
            ->join('ac.produit', 'p')
            ->where('ac.id = :liste_id')
            ->andWhere('p.id = :produit_id')
            ->setParameter('liste_id', $liste_id)
            ->setParameter('produit_id', $produit_id)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 