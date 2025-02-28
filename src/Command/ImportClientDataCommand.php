<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class ImportClientDataCommand extends Command
{
    protected static $defaultName = 'app:import-clients';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbPath = __DIR__ . '/../../var/data/data.db';
        $output->writeln("Connexion à la base de données SQLite : $dbPath");

        try {
            $pdo = new PDO("sqlite:$dbPath");
            
            // Vider la table avant l'import
            $pdo->exec('DELETE FROM client');
            
            // Données des clients
            $clients = [
                [1, 'Dubois', 'Marie', 'marie.dubois@email.fr', '06 12 34 56 78', '15 rue des Lilas, 75011 Paris', 'Allergie pénicilline', '1 85 12 34 567 890 21', 0],
                [2, 'Martin', 'Thomas', 'thomas.martin@email.fr', '07 23 45 67 89', '8 avenue Victor Hugo, 69002 Lyon', null, '2 85 23 45 678 901 32', 0],
                [3, 'Bernard', 'Sophie', 'sophie.bernard@email.fr', '06 34 56 78 90', '25 rue de la République, 13001 Marseille', 'Diabétique type 2', '1 75 34 56 789 012 43', 0],
                [4, 'Petit', 'Lucas', 'lucas.petit@email.fr', '07 45 67 89 01', '12 boulevard Gambetta, 33000 Bordeaux', null, '1 65 45 67 890 123 54', 1],
                [5, 'Robert', 'Emma', 'emma.robert@email.fr', '06 56 78 90 12', '5 rue du Commerce, 44000 Nantes', 'Hypertension', '2 55 56 78 901 234 65', 0],
                [6, 'Richard', 'Louis', 'louis.richard@email.fr', '07 67 89 01 23', '18 rue des Carmes, 31000 Toulouse', null, '1 95 67 89 012 345 76', 0],
                [7, 'Moreau', 'Chloé', 'chloe.moreau@email.fr', '06 78 90 12 34', '3 place Bellecour, 69002 Lyon', 'Allergie aspirine', '2 85 78 90 123 456 87', 0],
                [8, 'Simon', 'Gabriel', 'gabriel.simon@email.fr', '07 89 01 23 45', '45 rue de la Paix, 75002 Paris', null, '1 75 89 01 234 567 98', 1],
                [9, 'Laurent', 'Alice', 'alice.laurent@email.fr', '06 90 12 34 56', '7 rue Saint-Pierre, 76000 Rouen', 'Asthmatique', '2 65 90 12 345 678 09', 0],
                [10, 'Michel', 'Arthur', 'arthur.michel@email.fr', '07 01 23 45 67', '22 avenue Jean Jaurès, 59000 Lille', null, '1 55 01 23 456 789 10', 0],
                [11, 'Leroy', 'Louise', 'louise.leroy@email.fr', '06 12 34 56 78', '14 rue des Roses, 67000 Strasbourg', null, '2 95 12 34 567 890 21', 0],
                [12, 'Roux', 'Jules', 'jules.roux@email.fr', '07 23 45 67 89', '9 place de la Mairie, 35000 Rennes', 'Insuffisant cardiaque', '1 85 23 45 678 901 32', 1],
                [13, 'David', 'Léa', 'lea.david@email.fr', '06 34 56 78 90', '31 rue de la Gare, 21000 Dijon', null, '2 75 34 56 789 012 43', 0],
                [14, 'Bertrand', 'Hugo', 'hugo.bertrand@email.fr', '07 45 67 89 01', '6 avenue Foch, 57000 Metz', 'Allergie latex', '1 65 45 67 890 123 54', 0],
                [15, 'Vincent', 'Jade', 'jade.vincent@email.fr', '06 56 78 90 12', '28 boulevard de la Mer, 06000 Nice', null, '2 55 56 78 901 234 65', 0],
                [16, 'Fournier', 'Adam', 'adam.fournier@email.fr', '07 67 89 01 23', '11 rue Émile Zola, 84000 Avignon', 'Épileptique', '1 95 67 89 012 345 76', 0],
                [17, 'Morel', 'Sarah', 'sarah.morel@email.fr', '06 78 90 12 34', '4 place du Marché, 49000 Angers', null, '2 85 78 90 123 456 87', 0],
                [18, 'Girard', 'Raphaël', 'raphael.girard@email.fr', '07 89 01 23 45', '17 rue des Écoles, 63000 Clermont-Ferrand', null, '1 75 89 01 234 567 98', 1],
                [19, 'Andre', 'Eva', 'eva.andre@email.fr', '06 90 12 34 56', '23 avenue de la Libération, 87000 Limoges', 'Allergie iode', '2 65 90 12 345 678 09', 0],
                [20, 'Lefebvre', 'Nathan', 'nathan.lefebvre@email.fr', '07 01 23 45 67', '2 rue du Port, 17000 La Rochelle', null, '1 55 01 23 456 789 10', 0],
                [21, 'Mercier', 'Léo', 'leo.mercier@email.fr', '06 23 45 67 89', '19 rue Pasteur, 38000 Grenoble', 'Allergie arachides', '1 85 23 45 678 901 32', 0],
                [22, 'Bonnet', 'Camille', 'camille.bonnet@email.fr', '07 34 56 78 90', '7 place Stanislas, 54000 Nancy', null, '2 75 34 56 789 012 43', 0],
                [23, 'Rousseau', 'Maxime', 'maxime.rousseau@email.fr', '06 45 67 89 01', '3 rue de la Liberté, 21000 Dijon', 'Sous anticoagulants', '1 65 45 67 890 123 54', 1],
                [24, 'Blanc', 'Inès', 'ines.blanc@email.fr', '07 56 78 90 12', '12 avenue Foch, 83000 Toulon', null, '2 55 56 78 901 234 65', 0],
                [25, 'Guerin', 'Paul', 'paul.guerin@email.fr', '06 67 89 01 23', '8 rue Victor Hugo, 42000 Saint-Étienne', 'Diabétique type 1', '1 95 67 89 012 345 76', 0],
                [26, 'Boyer', 'Manon', 'manon.boyer@email.fr', '07 78 90 12 34', '15 boulevard Carnot, 06000 Nice', null, '2 85 78 90 123 456 87', 0],
                [27, 'Garnier', 'Antoine', 'antoine.garnier@email.fr', '06 89 01 23 45', '22 rue des Carmes, 45000 Orléans', 'Allergie sulfites', '1 75 89 01 234 567 98', 0],
                [28, 'Chevalier', 'Julia', 'julia.chevalier@email.fr', '07 90 12 34 56', '5 place du Marché, 51000 Reims', null, '2 65 90 12 345 678 09', 1],
                [29, 'Francois', 'Ethan', 'ethan.francois@email.fr', '06 01 23 45 67', '9 rue de la République, 80000 Amiens', 'Hypertension', '1 55 01 23 456 789 10', 0],
                [30, 'Legrand', 'Charlotte', 'charlotte.legrand@email.fr', '07 12 34 56 78', '17 avenue Gambetta, 33000 Bordeaux', null, '2 95 12 34 567 890 21', 0],
                [31, 'Gauthier', 'Victor', 'victor.gauthier@email.fr', '06 23 45 67 89', '4 rue Molière, 30000 Nîmes', 'Allergie pénicilline', '1 85 23 45 678 901 32', 0],
                [32, 'Perrin', 'Juliette', 'juliette.perrin@email.fr', '07 34 56 78 90', '11 place Bellecour, 69002 Lyon', null, '2 75 34 56 789 012 43', 1],
                [33, 'Robin', 'Samuel', 'samuel.robin@email.fr', '06 45 67 89 01', '28 rue Saint-Pierre, 14000 Caen', 'Épileptique', '1 65 45 67 890 123 54', 0],
                [34, 'Henry', 'Clara', 'clara.henry@email.fr', '07 56 78 90 12', '6 avenue Jean Jaurès, 37000 Tours', null, '2 55 56 78 901 234 65', 0],
                [35, 'Roussel', 'Alexandre', 'alexandre.roussel@email.fr', '06 67 89 01 23', '14 boulevard National, 13003 Marseille', 'Asthmatique', '1 95 67 89 012 345 76', 0],
                [36, 'Mathieu', 'Léna', 'lena.mathieu@email.fr', '07 78 90 12 34', '3 rue de la Paix, 44000 Nantes', null, '2 85 78 90 123 456 87', 0],
                [37, 'Fontaine', 'Théo', 'theo.fontaine@email.fr', '06 89 01 23 45', '20 place Kléber, 67000 Strasbourg', 'Insuffisant rénal', '1 75 89 01 234 567 98', 1],
                [38, 'Morin', 'Zoé', 'zoe.morin@email.fr', '07 90 12 34 56', '8 rue Émile Zola, 59000 Lille', null, '2 65 90 12 345 678 09', 0],
                [39, 'Clement', 'Baptiste', 'baptiste.clement@email.fr', '06 01 23 45 67', '16 avenue Foch, 57000 Metz', 'Allergie lactose', '1 55 01 23 456 789 10', 0],
                [40, 'Gautier', 'Maëlle', 'maelle.gautier@email.fr', '07 12 34 56 78', '25 rue du Commerce, 86000 Poitiers', null, '2 95 12 34 567 890 21', 0],
                [41, 'Nicolas', 'Enzo', 'enzo.nicolas@email.fr', '06 23 45 67 89', '7 place de la Bourse, 69002 Lyon', 'Hémophile', '1 85 23 45 678 901 32', 0],
                [42, 'Masson', 'Anaïs', 'anais.masson@email.fr', '07 34 56 78 90', '13 rue de la Gare, 35000 Rennes', null, '2 75 34 56 789 012 43', 1],
                [43, 'Gerard', 'Nolan', 'nolan.gerard@email.fr', '06 45 67 89 01', '31 boulevard des Belges, 76000 Rouen', 'Diabétique type 2', '1 65 45 67 890 123 54', 0],
                [44, 'Faure', 'Margaux', 'margaux.faure@email.fr', '07 56 78 90 12', '2 avenue de la Libération, 87000 Limoges', null, '2 55 56 78 901 234 65', 0],
                [45, 'Aubert', 'Mathis', 'mathis.aubert@email.fr', '06 67 89 01 23', '19 rue des Carmes, 21000 Dijon', 'Allergie iode', '1 95 67 89 012 345 76', 0],
                [46, 'Lemaire', 'Lucie', 'lucie.lemaire@email.fr', '07 78 90 12 34', '4 place Royale, 64000 Pau', null, '2 85 78 90 123 456 87', 0],
                [47, 'Dumont', 'Valentin', 'valentin.dumont@email.fr', '06 89 01 23 45', '27 rue de la République, 42000 Saint-Étienne', 'Sous cortisone', '1 75 89 01 234 567 98', 1],
                [48, 'Lefevre', 'Romane', 'romane.lefevre@email.fr', '07 90 12 34 56', '10 avenue Clemenceau, 34000 Montpellier', null, '2 65 90 12 345 678 09', 0],
                [49, 'Philippe', 'Axel', 'axel.philippe@email.fr', '06 01 23 45 67', '5 rue Saint-Martin, 21000 Dijon', 'Allergie pollen', '1 55 01 23 456 789 10', 0],
                [50, 'Giraud', 'Océane', 'oceane.giraud@email.fr', '07 12 34 56 78', '15 place de la Comédie, 69001 Lyon', null, '2 95 12 34 567 890 21', 0]
            ];

            // Préparation de la requête d'insertion
            $stmt = $pdo->prepare("INSERT INTO client (client_id, nom, prenom, email, telephone, adresse, commentaire, numero_carte_vitale, cheques_impayes) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Insertion des données
            $pdo->beginTransaction();
            
            foreach ($clients as $client) {
                $stmt->execute($client);
                $output->writeln("Client importé : " . $client[1] . " " . $client[2]);
            }

            $pdo->commit();
            $output->writeln("Données importées avec succès !");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $output->writeln("Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 