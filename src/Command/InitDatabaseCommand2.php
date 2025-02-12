<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class InitDatabaseCommand2 extends Command
{
    protected static $defaultName = 'app:init-database2';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbPath = __DIR__ . '/../../var/data/data.db';
        $output->writeln("Chemin de la base de données : $dbPath");

        try {
            $pdo = new PDO("sqlite:$dbPath");
            
            // Activer les contraintes de clés étrangères
            $pdo->exec('PRAGMA foreign_keys = ON');

            // Créer la table inventaire
            $pdo->exec('CREATE TABLE IF NOT EXISTS inventaire (
                produit_id INTEGER PRIMARY KEY,
                stock INTEGER NOT NULL,
                last_modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (produit_id) REFERENCES produit(produit_id) ON DELETE CASCADE
            )');

            // Créer le trigger pour la mise à jour automatique de last_modified
            $pdo->exec('DROP TRIGGER IF EXISTS update_inventaire_timestamp');
            $pdo->exec('
                CREATE TRIGGER update_inventaire_timestamp 
                AFTER UPDATE ON inventaire
                BEGIN
                    UPDATE inventaire 
                    SET last_modified = CURRENT_TIMESTAMP 
                    WHERE produit_id = NEW.produit_id;
                END
            ');

            $output->writeln("Table inventaire et trigger créés avec succès");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 