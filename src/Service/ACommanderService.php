<?php

namespace App\Service;

use App\Entity\ACommander;
use App\Repository\ACommanderRepository;
use App\Repository\InventaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Inventaire;
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
            $this->entityManager->persist($aCommander);
            $this->entityManager->flush();

            if (isset($data['produit']) && isset($data['quantite'])) {
                $ligneProduit = new LigneACommander();
                $ligneProduit->setACommander($aCommander);
                $ligneProduit->setProduit($data['produit']);
                $ligneProduit->setQuantite($data['quantite']);
                
                $this->entityManager->persist($ligneProduit);
                $this->entityManager->flush();
            }
            
            $connection->commit();
            return $aCommander;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    public function createSortedQuery(string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['p.nom', 'l.quantite', 'ac.id', 'ac.createdAt'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'ac.id';
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

        return $this->aCommanderRepository->createQueryBuilder('ac')
            ->select('ac, l, p')
            ->leftJoin('ac.lignes', 'l')
            ->leftJoin('l.produit', 'p')
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

            // Créer une seule liste pour tous les produits en stock bas
            $aCommander = new ACommander();
            $this->entityManager->persist($aCommander);
            $this->entityManager->flush();
            
            $this->logger->info('Début de la création depuis les stocks insuffisants');
            $this->logger->debug('Nombre de produits en stock insuffisant trouvés: ' . count($inventaires));
            
            foreach ($inventaires as $inventaire) {
                $produit = $inventaire->getProduit();
                if (!$produit || $produit->getDeclencherAlerte() !== 'oui') {
                    continue;
                }
                
                // Si le stock est inférieur ou égal au seuil d'alerte, commander la différence + 1
                // pour remonter au-dessus du seuil
                $quantiteACommander = $produit->getAlerte() - $inventaire->getStock() + 1;
                
                $ligneProduit = new LigneACommander();
                $ligneProduit->setACommander($aCommander);
                $ligneProduit->setProduit($produit);
                $ligneProduit->setQuantite($quantiteACommander);
                
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

    public function updateACommander(ACommander $aCommander, array $data): void
    {
        if (isset($data['lignes'])) {
            foreach ($data['lignes'] as $ligneData) {
                if (isset($ligneData['id'])) {
                    // Mise à jour d'une ligne existante
                    $ligne = $this->entityManager->getRepository(LigneACommander::class)->find($ligneData['id']);
                    if ($ligne && $ligne->getACommander() === $aCommander) {
                        if (isset($ligneData['quantite'])) {
                            $ligne->setQuantite($ligneData['quantite']);
                        }
                    }
                }
            }
        }
        $this->entityManager->flush();
    }

    public function deleteACommander(ACommander $aCommander): void
    {
        $this->entityManager->remove($aCommander);
        $this->entityManager->flush();
    }

    public function getListe(int $id): ?ACommander
    {
        return $this->aCommanderRepository->find($id);
    }

    public function getProduitsListeQuery(int $listeId, string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['p.nom', 'l.quantite'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'p.nom';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        return $this->entityManager->createQueryBuilder()
            ->select('l', 'p')
            ->from('App\Entity\LigneACommander', 'l')
            ->join('l.produit', 'p')
            ->where('l.aCommander = :listeId')
            ->setParameter('listeId', $listeId)
            ->orderBy($sortField, $sortOrder);
    }

    public function getProduitsListe(int $listeId, string $sortField, string $sortOrder): array
    {
        return $this->getProduitsListeQuery($listeId, $sortField, $sortOrder)
            ->getQuery()
            ->getResult();
    }

    public function getListesQuery(string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['ac.createdAt', 'ac.id'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'ac.createdAt';
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

        return $this->aCommanderRepository->createQueryBuilder('ac')
            ->select('ac')
            ->orderBy($sortField, $sortOrder);
    }

    public function getProduitFromListe(int $liste_id, int $produit_id): ?LigneACommander
    {
        return $this->entityManager->getRepository(LigneACommander::class)
            ->findOneBy([
                'aCommander' => $liste_id,
                'produit' => $produit_id
            ]);
    }
} 