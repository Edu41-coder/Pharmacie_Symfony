<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class InitDatabaseCommand3 extends Command
{
    protected static $defaultName = 'app:init-database3';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbPath = __DIR__ . '/../../var/data/data.db';
        $output->writeln("Chemin de la base de données : $dbPath");

        try {
            $pdo = new PDO("sqlite:$dbPath");
            
            // Activer les contraintes de clés étrangères
            $pdo->exec('PRAGMA foreign_keys = ON');

            // Créer la table client
            $pdo->exec('CREATE TABLE IF NOT EXISTS client (
                client_id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom VARCHAR(50) NOT NULL,
                prenom VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                telephone VARCHAR(20) DEFAULT NULL,
                adresse TEXT DEFAULT NULL,
                commentaire TEXT DEFAULT NULL,
                numero_carte_vitale VARCHAR(15) DEFAULT NULL,
                cheques_impayes BOOLEAN NOT NULL DEFAULT 0
            )');

            $output->writeln("Table client créée avec succès");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 