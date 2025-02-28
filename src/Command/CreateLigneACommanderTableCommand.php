<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLigneACommanderTableCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:create-ligne-a-commander-table')
             ->setDescription('Crée la table ligne_a_commander');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbPath = 'var/data/data.db';
        
        if (!file_exists($dbPath)) {
            $output->writeln('Base de données non trouvée : ' . $dbPath);
            return Command::FAILURE;
        }

        try {
            $pdo = new \PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Désactiver les contraintes de clé étrangère
            $pdo->exec('PRAGMA foreign_keys = OFF');
            
            $sql = "CREATE TABLE IF NOT EXISTS ligne_a_commander (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                a_commander_id INTEGER NOT NULL,
                produit_id INTEGER NOT NULL,
                quantite INTEGER NOT NULL,
                FOREIGN KEY (a_commander_id) REFERENCES a_commander(a_commander_id) ON DELETE CASCADE,
                FOREIGN KEY (produit_id) REFERENCES produit(produit_id) ON DELETE CASCADE
            )";
            
            $pdo->exec($sql);
            
            // Réactiver les contraintes de clé étrangère
            $pdo->exec('PRAGMA foreign_keys = ON');
            
            $output->writeln('Table ligne_a_commander créée avec succès !');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 