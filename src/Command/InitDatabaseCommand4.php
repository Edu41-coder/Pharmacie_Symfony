<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitDatabaseCommand4 extends Command
{
    protected function configure()
    {
        $this->setName('app:init-database4')
             ->setDescription('Crée les tables a_commander et creation_a_commander');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbPath = 'var/data/data.db';
        
        if (!file_exists($dbPath)) {
            $output->writeln('Base de données non trouvée : ' . $dbPath);
            return Command::FAILURE;
        }

        $pdo = new \PDO('sqlite:' . $dbPath);
        
        $sql = "
        DROP TABLE IF EXISTS a_commander;
        DROP TABLE IF EXISTS creation_commander;
        DROP TABLE IF EXISTS creation_a_commander;

        CREATE TABLE IF NOT EXISTS a_commander (
            a_commander_id INTEGER PRIMARY KEY AUTOINCREMENT,
            produit_id INTEGER NOT NULL,
            quantite INTEGER NOT NULL,
            FOREIGN KEY (produit_id) REFERENCES produit (id)
        );

        CREATE TABLE IF NOT EXISTS creation_a_commander (
            a_commander_id INTEGER NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (a_commander_id) REFERENCES a_commander (a_commander_id)
        );";

        try {
            $pdo->exec($sql);
            $output->writeln('Anciennes tables supprimées et nouvelles tables créées avec succès !');
            return Command::SUCCESS;
        } catch (\PDOException $e) {
            $output->writeln('Erreur lors de l\'opération : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 