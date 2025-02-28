<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearACommanderTablesCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:clear-a-commander')
             ->setDescription('Vide les tables a_commander et creation_a_commander');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbPath = 'var/data/data.db';
        
        if (!file_exists($dbPath)) {
            $output->writeln('Base de données non trouvée : ' . $dbPath);
            return Command::FAILURE;
        }

        try {
            // Attendre que la base de données soit disponible
            $maxAttempts = 5;
            $attempt = 0;
            
            while ($attempt < $maxAttempts) {
                try {
                    $pdo = new \PDO('sqlite:' . $dbPath);
                    $pdo->setAttribute(\PDO::ATTR_TIMEOUT, 5); // Timeout de 5 secondes
                    
                    // Désactiver temporairement les contraintes de clé étrangère
                    $pdo->exec('PRAGMA foreign_keys = OFF');
                    
                    // Vérifier la structure des tables
                    $output->writeln('Vérification de la structure des tables...');
                    
                    // Supprimer les données dans l'ordre correct
                    $sql = "
                        DELETE FROM creation_a_commander;
                        DELETE FROM a_commander;
                    ";
                    
                    $pdo->exec($sql);
                    
                    // Réactiver les contraintes de clé étrangère
                    $pdo->exec('PRAGMA foreign_keys = ON');
                    
                    // Fermer la connexion
                    $pdo = null;
                    
                    $output->writeln('Tables vidées avec succès !');
                    return Command::SUCCESS;
                    
                } catch (\PDOException $e) {
                    if (strpos($e->getMessage(), 'database is locked') !== false) {
                        $attempt++;
                        $output->writeln("Tentative $attempt : Base de données verrouillée, nouvelle tentative dans 1 seconde...");
                        sleep(1);
                        continue;
                    }
                    throw $e;
                }
            }
            
            throw new \RuntimeException("Impossible d'accéder à la base de données après $maxAttempts tentatives");
            
        } catch (\Exception $e) {
            $output->writeln('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}